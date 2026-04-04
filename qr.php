<?php
/**
 * qr.php
 * -------
 * Student-facing page that shows their appointment QR code.
 *
 * HOW IT WORKS:
 *  - The QR code is the unique token stored in Appointments.qr_code column.
 *  - If qr_code is NULL/empty for an appointment (e.g. old data), we generate
 *    one on the fly and save it to the DB so it always exists.
 *  - QR image is generated via the free Google Charts API — no PHP library needed.
 *  - Staff scan this QR on checkin.php to check the student in.
 *
 * MODES:
 *  - qr.php?id=X  → show QR for appointment ID X (must belong to logged-in user)
 *  - qr.php       → auto-load next upcoming booked appointment
 */
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php';

$appointment = null;

// ── Load appointment ──────────────────────────────────────────────────────────
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT a.*, s.name AS service_name, s.description AS service_desc
        FROM Appointments a
        JOIN Services s ON a.service_id = s.id
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([(int)$_GET['id'], $_SESSION['user_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        echo '
        <div class="container mt-5">
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-xmark me-2"></i>
                Appointment not found or you do not have permission to view it.
            </div>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fa-solid fa-house me-2"></i>Back to Dashboard
            </a>
        </div>';
        require_once 'includes/footer.php';
        exit;
    }
} else {
    // Auto-load next upcoming booked appointment for this student
    $stmt = $pdo->prepare("
        SELECT a.*, s.name AS service_name, s.description AS service_desc
        FROM Appointments a
        JOIN Services s ON a.service_id = s.id
        WHERE a.user_id = ?
          AND a.date >= CURDATE()
          AND a.status = 'booked'
        ORDER BY a.date ASC, a.time ASC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ── Ensure qr_code token exists (generate + save if missing) ──────────────────
if ($appointment && empty($appointment['qr_code'])) {
    $token = bin2hex(random_bytes(16)); // 32-char unique hex token
    $fix = $pdo->prepare("UPDATE Appointments SET qr_code = ? WHERE id = ?");
    $fix->execute([$token, $appointment['id']]);
    $appointment['qr_code'] = $token;
}

// ── Build QR image URL (Google Charts — free, no install) ────────────────────
$qr_image_url = null;
if ($appointment && !empty($appointment['qr_code'])) {
    $qr_image_url = "https://chart.googleapis.com/chart?cht=qr&chs=280x280&chl="
                  . urlencode($appointment['qr_code'])
                  . "&choe=UTF-8&chld=M|2";
}

// ── Queue info (if already checked in) ───────────────────────────────────────
$queueInfo = null;
if ($appointment) {
    $qStmt = $pdo->prepare("SELECT * FROM Queue WHERE appointment_id = ?");
    $qStmt->execute([$appointment['id']]);
    $queueInfo = $qStmt->fetch(PDO::FETCH_ASSOC);
}

// ── Other upcoming appointments for sidebar ───────────────────────────────────
$others = [];
if ($appointment) {
    $otherStmt = $pdo->prepare("
        SELECT a.id, a.date, a.time, a.status, s.name AS service_name
        FROM Appointments a
        JOIN Services s ON a.service_id = s.id
        WHERE a.user_id = ?
          AND a.date >= CURDATE()
          AND a.status = 'booked'
          AND a.id != ?
        ORDER BY a.date ASC
        LIMIT 4
    ");
    $otherStmt->execute([$_SESSION['user_id'], $appointment['id']]);
    $others = $otherStmt->fetchAll(PDO::FETCH_ASSOC);
}

// ── Status badge config ───────────────────────────────────────────────────────
$statusConfig = [
    'booked'     => ['class' => 'primary', 'icon' => 'calendar-check',   'label' => 'Booked'],
    'checked-in' => ['class' => 'info',    'icon' => 'right-to-bracket', 'label' => 'Checked In'],
    'completed'  => ['class' => 'success', 'icon' => 'check-double',     'label' => 'Completed'],
    'cancelled'  => ['class' => 'danger',  'icon' => 'ban',              'label' => 'Cancelled'],
];
$statusInfo = $appointment
    ? ($statusConfig[$appointment['status']] ?? ['class' => 'secondary', 'icon' => 'circle', 'label' => ucfirst($appointment['status'])])
    : null;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mt-4 mb-4">
    <h2 class="text-white">
        <i class="fa-solid fa-qrcode me-2"></i>My Check-In QR Code
    </h2>
    <a href="dashboard.php" class="btn btn-light">
        <i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<?php if (!$appointment): ?>
<!-- ── No appointment found ─────────────────────────────────────────────── -->
<div class="row justify-content-center">
    <div class="col-md-6 text-center">
        <div class="card border-0 shadow-sm p-5">
            <i class="fa-solid fa-calendar-xmark fa-4x text-muted opacity-50 mb-4"></i>
            <h4 class="fw-bold mb-2">No Upcoming Appointments</h4>
            <p class="text-muted mb-4">You don't have any booked appointments. Book one first to get your QR code.</p>
            <a href="book.php" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-calendar-plus me-2"></i>Book an Appointment
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ── QR Code display ──────────────────────────────────────────────────── -->
<div class="row justify-content-center g-4">

    <!-- Main QR Card -->
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm mb-3">

            <!-- Card Header: service + status -->
            <div class="card-header bg-white text-center py-4 border-bottom">
                <span class="badge bg-<?= $statusInfo['class'] ?> px-3 py-2 mb-2">
                    <i class="fa-solid fa-<?= $statusInfo['icon'] ?> me-1"></i>
                    <?= $statusInfo['label'] ?>
                </span>
                <h4 class="fw-bold mb-0 mt-1"><?= htmlspecialchars($appointment['service_name']) ?></h4>
                <?php if (!empty($appointment['service_desc'])): ?>
                    <p class="text-muted small mb-0 mt-1"><?= htmlspecialchars($appointment['service_desc']) ?></p>
                <?php endif; ?>
            </div>

            <div class="card-body text-center p-4">

                <?php if ($appointment['status'] === 'completed'): ?>
                <!-- ── COMPLETED ─────────────────────────────────────────── -->
                <div class="py-4">
                    <i class="fa-solid fa-circle-check fa-5x text-success mb-3"></i>
                    <h5 class="text-success fw-bold">Appointment Completed</h5>
                    <p class="text-muted small">This appointment is done. No check-in needed.</p>
                </div>

                <?php elseif ($appointment['status'] === 'checked-in'): ?>
                <!-- ── ALREADY CHECKED IN ────────────────────────────────── -->
                <div class="bg-info bg-opacity-10 rounded-3 py-4 px-3 mb-3">
                    <p class="text-muted small mb-1">Your Queue Number</p>
                    <h1 class="display-1 fw-bold text-info mb-2">
                        #<?= $queueInfo ? (int)$queueInfo['position'] : '—' ?>
                    </h1>
                    <?php if ($queueInfo): ?>
                        <?php if ($queueInfo['status'] === 'serving'): ?>
                            <span class="badge bg-success px-3 py-2 fs-6">
                                <i class="fa-solid fa-circle-play me-1"></i>You're being served now!
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                <i class="fa-solid fa-hourglass-half me-1"></i>Waiting — please stay nearby
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <p class="text-muted small">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    You are checked in. Wait until your number is called by the staff.
                </p>

                <?php elseif ($appointment['status'] === 'booked'): ?>
                <!-- ── BOOKED — Show QR code ──────────────────────────────── -->
                    <?php if ($qr_image_url): ?>
                        <!-- QR Image -->
                        <div class="bg-white border rounded-3 p-3 d-inline-block mb-3 shadow-sm" id="qr-box">
                            <img
                                src="<?= htmlspecialchars($qr_image_url) ?>"
                                alt="QR Code"
                                class="img-fluid"
                                style="max-width: 240px; width: 100%;"
                                id="qr-img"
                                onerror="showFallback()"
                            >
                        </div>
                        <!-- Fallback token (shown if image fails to load) -->
                        <div id="qr-fallback" class="bg-light border rounded-3 p-4 mb-3 d-none">
                            <p class="text-muted small mb-1">Your check-in code:</p>
                            <h5 class="fw-bold font-monospace text-primary text-break">
                                <?= htmlspecialchars($appointment['qr_code']) ?>
                            </h5>
                            <p class="text-muted small mb-0">Show this code to staff if QR image doesn't load.</p>
                        </div>
                    <?php else: ?>
                        <!-- No QR token — show raw code -->
                        <div class="bg-light border rounded-3 p-4 mb-3">
                            <p class="text-muted small mb-1">Your check-in code:</p>
                            <h5 class="fw-bold font-monospace text-primary text-break">
                                <?= htmlspecialchars($appointment['qr_code']) ?>
                            </h5>
                        </div>
                    <?php endif; ?>
                    <p class="text-muted small">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                        Show this QR code to staff or scan it at the check-in kiosk on the day of your appointment.
                    </p>
                <?php endif; ?>

            </div>

            <!-- Appointment details footer -->
            <div class="card-footer bg-light border-0 px-4 py-3">
                <div class="row text-center g-0">
                    <div class="col-6 border-end">
                        <p class="text-muted small mb-1">Date</p>
                        <p class="fw-bold mb-0 small">
                            <i class="fa-regular fa-calendar me-1"></i>
                            <?= date('M d, Y', strtotime($appointment['date'])) ?>
                        </p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Time</p>
                        <p class="fw-bold mb-0 small">
                            <i class="fa-regular fa-clock me-1"></i>
                            <?= date('h:i A', strtotime($appointment['time'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-grid gap-2">
            <?php if ($appointment['status'] === 'booked'): ?>
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fa-solid fa-print me-2"></i>Print / Save QR Code
                </button>
            <?php endif; ?>
            <a href="my_appointments.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-list me-2"></i>All My Appointments
            </a>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fa-solid fa-house me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Other upcoming appointments sidebar -->
    <?php if (!empty($others)): ?>
    <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-calendar-days me-2 text-primary"></i>Other Upcoming
                </h6>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($others as $other): ?>
                    <a href="qr.php?id=<?= (int)$other['id'] ?>"
                       class="list-group-item list-group-item-action py-3">
                        <div class="fw-bold small"><?= htmlspecialchars($other['service_name']) ?></div>
                        <div class="text-muted small">
                            <i class="fa-regular fa-calendar me-1"></i>
                            <?= date('M d', strtotime($other['date'])) ?>
                            &nbsp;·&nbsp;
                            <?= date('h:i A', strtotime($other['time'])) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php endif; ?>

<!-- Print styles -->
<style>
    @media print {
        nav, .btn, footer, .card-footer, h2,
        .d-flex.justify-content-between,
        .col-md-4, .col-lg-3 { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        body { background: white !important; }
        #qr-box { border: 2px solid #000 !important; }
    }
</style>

<script>
    // If QR image fails to load (e.g. offline), show raw token fallback
    function showFallback() {
        document.getElementById('qr-box').classList.add('d-none');
        document.getElementById('qr-fallback').classList.remove('d-none');
    }
</script>

<?php require_once 'includes/footer.php'; ?>