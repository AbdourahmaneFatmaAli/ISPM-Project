<?php
require_once 'includes/auth_check.php';
require_role('staff');
require_once 'config/database.php';
require_once 'includes/header.php';

$today = date('Y-m-d');

// Get the student currently being served
$servingStmt = $pdo->prepare("
    SELECT q.*, a.time, a.id as appointment_id,
           s.name as service_name,
           u.name as student_name, u.id as student_id, u.email as student_email
    FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    JOIN Services s ON a.service_id = s.id
    JOIN Users u ON a.user_id = u.id
    WHERE a.date = ? AND q.status = 'serving'
    ORDER BY q.position ASC
    LIMIT 1
");
$servingStmt->execute([$today]);
$current = $servingStmt->fetch(PDO::FETCH_ASSOC);

// Get waiting queue
$waitingStmt = $pdo->prepare("
    SELECT q.*, a.time, a.id as appointment_id,
           s.name as service_name,
           u.name as student_name, u.id as student_id, u.email as student_email
    FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    JOIN Services s ON a.service_id = s.id
    JOIN Users u ON a.user_id = u.id
    WHERE a.date = ? AND q.status = 'waiting'
    ORDER BY q.position ASC
");
$waitingStmt->execute([$today]);
$waiting = $waitingStmt->fetchAll(PDO::FETCH_ASSOC);

// Get completed count for today
$doneStmt = $pdo->prepare("
    SELECT COUNT(*) FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    WHERE a.date = ? AND q.status = 'done'
");
$doneStmt->execute([$today]);
$doneCount = $doneStmt->fetchColumn();

// Get total checked-in today
$totalStmt = $pdo->prepare("
    SELECT COUNT(*) FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    WHERE a.date = ?
");
$totalStmt->execute([$today]);
$totalToday = $totalStmt->fetchColumn();
?>

<!-- Staff Header -->
<div class="d-flex justify-content-between align-items-center mt-4 mb-4">
    <div>
        <h2 class="text-white mb-1">
            <i class="fa-solid fa-desktop me-2"></i>Queue Control Panel
        </h2>
        <p class="text-white opacity-75 mb-0">
            <i class="far fa-calendar me-2"></i><?= date('l, F j, Y') ?> &nbsp;|&nbsp;
            Staff: <?= htmlspecialchars($_SESSION['name'] ?? 'Staff') ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="checkin.php" class="btn btn-outline-light">
            <i class="fa-solid fa-qrcode me-2"></i>Kiosk / Scan QR
        </a>
        <a href="staff_dashboard.php" class="btn btn-light">
            <i class="fa-solid fa-arrows-rotate me-2"></i>Refresh
        </a>
    </div>
</div>

<!-- Today's Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="card-body">
                <div class="text-warning mb-2"><i class="fa-solid fa-hourglass-half fa-2x"></i></div>
                <h3 class="display-6 fw-bold mb-1"><?= count($waiting) ?></h3>
                <p class="text-muted mb-0 small">Waiting Now</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="card-body">
                <div class="text-success mb-2"><i class="fa-solid fa-circle-play fa-2x"></i></div>
                <h3 class="display-6 fw-bold mb-1"><?= $current ? 1 : 0 ?></h3>
                <p class="text-muted mb-0 small">Currently Serving</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="card-body">
                <div class="text-info mb-2"><i class="fa-solid fa-check-double fa-2x"></i></div>
                <h3 class="display-6 fw-bold mb-1"><?= $doneCount ?></h3>
                <p class="text-muted mb-0 small">Completed Today</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3 h-100">
            <div class="card-body">
                <div class="text-primary mb-2"><i class="fa-solid fa-users fa-2x"></i></div>
                <h3 class="display-6 fw-bold mb-1"><?= $totalToday ?></h3>
                <p class="text-muted mb-0 small">Total Checked-In</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- LEFT: Currently Serving + Call Next -->
    <div class="col-lg-5">

        <!-- Currently Serving Card -->
        <div class="card border-0 shadow-sm border-top border-success border-4 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-success">
                    <i class="fa-solid fa-circle-play me-2"></i>Currently Serving
                </h5>
            </div>
            <div class="card-body text-center p-4">
                <?php if ($current): ?>
                    <!-- Big queue number -->
                    <div class="bg-success bg-opacity-10 rounded-3 py-4 mb-3">
                        <p class="text-muted small mb-1">Queue Number</p>
                        <h1 class="display-1 fw-bold text-success mb-0"><?= (int)$current['position'] ?></h1>
                    </div>
                    <!-- Student info -->
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($current['student_name']) ?></h4>
                    <p class="text-muted mb-1">
                        <i class="fa-solid fa-briefcase-medical me-1"></i>
                        <?= htmlspecialchars($current['service_name']) ?>
                    </p>
                    <p class="text-muted small">
                        <i class="fa-regular fa-clock me-1"></i>
                        Appointment at <?= date('h:i A', strtotime($current['time'])) ?>
                    </p>
                    <hr>
                    <!-- Mark as completed -->
                    <form action="manage_queue.php" method="POST">
                        <input type="hidden" name="queue_id"   value="<?= (int)$current['id'] ?>">
                        <input type="hidden" name="action"     value="complete">
                        <input type="hidden" name="student_id" value="<?= (int)$current['student_id'] ?>">
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fa-solid fa-check-double me-2"></i>Mark as Completed
                        </button>
                    </form>
                <?php else: ?>
                    <div class="py-4">
                        <i class="fa-solid fa-mug-hot fa-3x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No one being served</h5>
                        <p class="text-muted small">
                            <?= count($waiting) > 0
                                ? 'Click "Call Next Student" below to begin.'
                                : 'The queue is empty for today.' ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Call Next Student Card -->
        <div class="card border-0 shadow-sm border-top border-warning border-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-warning">
                    <i class="fa-solid fa-bullhorn me-2"></i>Call Next Student
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($waiting) && !$current): ?>
                    <?php $next = $waiting[0]; ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width:56px;height:56px;min-width:56px;">
                            <span class="fw-bold fs-4 text-warning"><?= (int)$next['position'] ?></span>
                        </div>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($next['student_name']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($next['service_name']) ?></div>
                            <div class="text-muted small">
                                <i class="fa-regular fa-clock me-1"></i><?= date('h:i A', strtotime($next['time'])) ?>
                            </div>
                        </div>
                    </div>
                    <form action="manage_queue.php" method="POST">
                        <input type="hidden" name="queue_id"   value="<?= (int)$next['id'] ?>">
                        <input type="hidden" name="action"     value="call">
                        <input type="hidden" name="student_id" value="<?= (int)$next['student_id'] ?>">
                        <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">
                            <i class="fa-solid fa-arrow-right me-2"></i>Call Student #<?= (int)$next['position'] ?>
                        </button>
                    </form>
                <?php elseif ($current): ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fa-solid fa-lock fa-2x mb-2 d-block opacity-50"></i>
                        <p class="mb-0 small">Complete the current session before calling the next student.</p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                        <p class="mb-0 small">No students waiting in queue.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- RIGHT: Full Waiting Queue -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm border-top border-primary border-4 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="fa-solid fa-list-ol me-2"></i>Waiting Queue
                </h5>
                <span class="badge bg-primary fs-6"><?= count($waiting) ?> in queue</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4" style="width:60px">#</th>
                                <th>Student</th>
                                <th>Service</th>
                                <th>Appt. Time</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($waiting)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                        No students waiting in queue today.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($waiting as $index => $w): ?>
                                    <tr class="<?= $index === 0 && !$current ? 'table-warning' : '' ?>">
                                        <td class="px-4">
                                            <span class="badge bg-<?= $index === 0 ? 'warning text-dark' : 'secondary' ?> fs-6 px-3 py-2">
                                                <?= (int)$w['position'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($w['student_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($w['student_email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($w['service_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fa-regular fa-clock me-1 text-muted"></i>
                                            <?= date('h:i A', strtotime($w['time'])) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($index === 0 && !$current): ?>
                                                <span class="badge bg-warning text-dark">Next Up</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Waiting</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Currently serving row at bottom if active -->
            <?php if ($current): ?>
                <div class="card-footer bg-success bg-opacity-10 border-0 py-3 px-4">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-3 px-3 py-2 fs-6"><?= (int)$current['position'] ?></span>
                        <div>
                            <span class="fw-bold"><?= htmlspecialchars($current['student_name']) ?></span>
                            <span class="text-muted ms-2 small">— currently at counter</span>
                        </div>
                        <span class="badge bg-success ms-auto">
                            <i class="fa-solid fa-circle-play me-1"></i>Serving
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Bottom: Quick links for staff -->
<div class="row mt-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 px-4">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted me-2 small fw-bold">STAFF TOOLS:</span>
                    <a href="checkin.php" class="btn btn-sm btn-outline-info">
                        <i class="fa-solid fa-qrcode me-1"></i>Scan QR / Kiosk
                    </a>
                    <a href="appointments.php" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-calendar-days me-1"></i>All Appointments
                    </a>
                    <a href="students.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-users me-1"></i>Students List
                    </a>
                    <a href="staff_dashboard.php" class="btn btn-sm btn-outline-success ms-auto">
                        <i class="fa-solid fa-arrows-rotate me-1"></i>Refresh Queue
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>