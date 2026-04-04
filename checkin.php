<?php
/**
 * checkin.php
 * -----------
 * Staff/Kiosk page. Staff scan or type the student's QR token to check them in.
 *
 * FLOW:
 *  1. Staff scans student's QR code (from qr.php)         → token submitted here
 *  2. Token is matched against Appointments.qr_code
 *  3. Appointment status → 'checked-in'
 *  4. Student inserted into Queue table with next position
 *  5. Notification sent to student
 *  6. Success card shown with student name + queue number
 *  7. Staff clicks "Scan Next" or goes back to staff_dashboard.php
 *
 * CONNECTS TO:
 *  - staff_dashboard.php  (queue monitor — shows who to call next)
 *  - manage_queue.php     (call / complete actions)
 *  - qr.php               (student shows this to get scanned here)
 */
require_once 'includes/auth_check.php';
require_role('staff');
require_once 'config/database.php';

$error     = '';
$checkedIn = null; // holds full result data on success

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    $qr_code = trim($_POST['qr_code']);

    if (empty($qr_code)) {
        $error = "Please scan or enter a QR code.";
    } else {
        // ── Look up appointment by QR token ──────────────────────────────────
        $stmt = $pdo->prepare("
            SELECT a.*,
                   s.name  AS service_name,
                   u.name  AS student_name,
                   u.id    AS student_user_id,
                   u.email AS student_email
            FROM Appointments a
            JOIN Services s ON a.service_id  = s.id
            JOIN Users    u ON a.user_id     = u.id
            WHERE a.qr_code = ?
        ");
        $stmt->execute([$qr_code]);
        $appt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appt) {
            $error = "Invalid QR code. No appointment found for this code.";

        } elseif ($appt['status'] === 'completed') {
            $error = "This appointment is already <strong>completed</strong>. No check-in needed.";

        } elseif ($appt['status'] === 'checked-in') {
            // Already checked in — show their queue number instead of an error
            $qStmt = $pdo->prepare("SELECT position, status FROM Queue WHERE appointment_id = ?");
            $qStmt->execute([$appt['id']]);
            $existing = $qStmt->fetch(PDO::FETCH_ASSOC);
            $pos = $existing ? (int)$existing['position'] : '?';
            $error = "This student is <strong>already checked in</strong>. Queue number: <strong>#$pos</strong>.";

        } elseif ($appt['status'] !== 'booked') {
            $error = "Cannot check in — appointment status is <strong>" . ucfirst($appt['status']) . "</strong>.";

        } else {
            // ── VALID — Process check-in ──────────────────────────────────────

            // 1. Update appointment status → 'checked-in'
            $upd = $pdo->prepare("UPDATE Appointments SET status = 'checked-in' WHERE id = ?");
            $upd->execute([$appt['id']]);

            // 2. Calculate next queue position for today
            $posStmt = $pdo->prepare("
                SELECT MAX(q.position) AS max_pos
                FROM Queue q
                JOIN Appointments a ON q.appointment_id = a.id
                WHERE a.date = ?
            ");
            $posStmt->execute([$appt['date']]);
            $posRow   = $posStmt->fetch(PDO::FETCH_ASSOC);
            $position = ($posRow['max_pos'] ? (int)$posRow['max_pos'] : 0) + 1;

            // 3. Insert into Queue as 'waiting'
            $qInsert = $pdo->prepare("
                INSERT INTO Queue (appointment_id, position, status)
                VALUES (?, ?, 'waiting')
            ");
            $qInsert->execute([$appt['id'], $position]);

            // 4. Send in-app notification to student
            $msg    = "You have checked in successfully! Your queue number is #$position. Please wait until your number is called.";
            $notify = $pdo->prepare("
                INSERT INTO Notifications (user_id, message, type)
                VALUES (?, ?, 'in-app')
            ");
            $notify->execute([$appt['student_user_id'], $msg]);

            // 5. Build result object for display
            $checkedIn                 = $appt;
            $checkedIn['queue_position'] = $position;
        }
    }
}

// ── Today's stats for footer display ────────────────────────────────────────
$todayCount = $pdo->prepare("
    SELECT COUNT(*) FROM Appointments
    WHERE date = CURDATE() AND status IN ('checked-in', 'completed')
");
$todayCount->execute();
$todayTotal = (int)$todayCount->fetchColumn();

$waitingCount = $pdo->prepare("
    SELECT COUNT(*) FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    WHERE a.date = CURDATE() AND q.status = 'waiting'
");
$waitingCount->execute();
$waitingTotal = (int)$waitingCount->fetchColumn();

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mt-4 mb-4">
    <div>
        <h2 class="text-white mb-1">
            <i class="fa-solid fa-qrcode me-2"></i>Kiosk Check-In
        </h2>
        <p class="text-white opacity-75 mb-0 small">
            <i class="fa-regular fa-calendar me-1"></i><?= date('l, F j, Y') ?>
        </p>
    </div>
    <a href="staff_dashboard.php" class="btn btn-light">
        <i class="fa-solid fa-desktop me-2"></i>Queue Monitor
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">

        <?php if ($checkedIn): ?>
        <!-- ── SUCCESS CARD ───────────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm border-top border-success border-4 mb-4">
            <div class="card-body text-center p-5">

                <!-- Success icon -->
                <div class="text-success mb-3">
                    <i class="fa-solid fa-circle-check fa-5x"></i>
                </div>
                <h3 class="fw-bold text-success mb-1">Checked In!</h3>
                <p class="text-muted mb-4">Student added to queue successfully</p>

                <!-- Big queue number -->
                <div class="bg-success bg-opacity-10 rounded-3 py-4 px-3 mb-4">
                    <p class="text-muted small mb-1">Queue Number Assigned</p>
                    <h1 class="display-1 fw-bold text-success mb-0">
                        #<?= (int)$checkedIn['queue_position'] ?>
                    </h1>
                </div>

                <!-- Student details -->
                <div class="text-start border rounded-3 p-3 mb-4 bg-light">
                    <div class="row g-2">
                        <div class="col-5 text-muted small">Student</div>
                        <div class="col-7 fw-bold small"><?= htmlspecialchars($checkedIn['student_name']) ?></div>

                        <div class="col-5 text-muted small">Email</div>
                        <div class="col-7 small text-break"><?= htmlspecialchars($checkedIn['student_email']) ?></div>

                        <div class="col-5 text-muted small">Service</div>
                        <div class="col-7 fw-bold small"><?= htmlspecialchars($checkedIn['service_name']) ?></div>

                        <div class="col-5 text-muted small">Appt. Time</div>
                        <div class="col-7 small">
                            <i class="fa-regular fa-clock me-1"></i>
                            <?= date('h:i A', strtotime($checkedIn['time'])) ?>
                            &nbsp;·&nbsp;
                            <?= date('M d, Y', strtotime($checkedIn['date'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2">
                    <a href="checkin.php" class="btn btn-success btn-lg">
                        <i class="fa-solid fa-qrcode me-2"></i>Scan Next Student
                    </a>
                    <a href="staff_dashboard.php" class="btn btn-outline-primary">
                        <i class="fa-solid fa-desktop me-2"></i>View Queue Monitor
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ── SCAN FORM ──────────────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center p-5">

                <div class="text-primary mb-3">
                    <i class="fa-solid fa-qrcode fa-4x opacity-75"></i>
                </div>
                <h3 class="fw-bold mb-1">Scan Student QR Code</h3>
                <p class="text-muted mb-4">
                    Point the barcode scanner at the student's QR code,<br>or type the code manually below.
                </p>

                <!-- Error alert -->
                <?php if ($error): ?>
                    <div class="alert alert-danger text-start d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-xmark mt-1 flex-shrink-0"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <!-- Input form -->
                <form action="" method="POST">
                    <div class="mb-3">
                        <input
                            type="text"
                            name="qr_code"
                            id="qr_input"
                            class="form-control form-control-lg text-center <?= $error ? 'is-invalid' : '' ?>"
                            placeholder="Click here, then scan QR code"
                            autofocus
                            required
                            autocomplete="off"
                            style="letter-spacing: 1px;"
                        >
                        <div class="form-text mt-2">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            The scanner will auto-submit when it reads the QR code.
                            You can also type the code and press Enter.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Process Check-In
                    </button>
                </form>

                <hr class="my-4">

                <div class="d-flex gap-2">
                    <a href="staff_dashboard.php" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-desktop me-2"></i>Queue Monitor
                    </a>
                    <a href="appointments.php" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-calendar-days me-2"></i>Appointments
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Live stats bar -->
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 px-4">
                <div class="row text-center g-0">
                    <div class="col-6 border-end">
                        <p class="text-muted small mb-1">Checked In Today</p>
                        <h4 class="fw-bold text-primary mb-0"><?= $todayTotal ?></h4>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Currently Waiting</p>
                        <h4 class="fw-bold text-warning mb-0"><?= $waitingTotal ?></h4>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Auto-focus + auto-submit on barcode scan (scanners send Enter after the code) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('qr_input');
        if (input) {
            input.focus();
            // Most barcode scanners append a newline — the form submits automatically on Enter.
            // This also handles manual typing + Enter key.
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>