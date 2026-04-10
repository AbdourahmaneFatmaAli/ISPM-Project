<?php
require_once 'includes/auth_check.php';
require_role('staff');
require_once 'config/database.php';
require_once 'includes/header.php';

$today = date('Y-m-d');
$staff_name = $_SESSION['name'] ?? '';

// SQL queries remain the same for functionality
$servingStmt = $pdo->prepare("
    SELECT q.*, a.time, a.id as appointment_id,
           s.name as service_name,
           u.name as student_name, u.id as student_id, u.email as student_email
    FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    JOIN Services s ON a.service_id = s.id
    JOIN Users u ON a.user_id = u.id
    WHERE a.date = ? AND q.status = 'serving' AND s.faculty_name = ?
    ORDER BY q.position ASC
    LIMIT 1
");
$servingStmt->execute([$today, $staff_name]);
$current = $servingStmt->fetch(PDO::FETCH_ASSOC);

$waitingStmt = $pdo->prepare("
    SELECT q.*, a.time, a.id as appointment_id,
           s.name as service_name,
           u.name as student_name, u.id as student_id, u.email as student_email
    FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    JOIN Services s ON a.service_id = s.id
    JOIN Users u ON a.user_id = u.id
    WHERE a.date = ? AND q.status = 'waiting' AND s.faculty_name = ?
    ORDER BY q.position ASC
");
$waitingStmt->execute([$today, $staff_name]);
$waiting = $waitingStmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$doneStmt = $pdo->prepare("
    SELECT COUNT(*) FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    JOIN Services s ON a.service_id = s.id
    WHERE a.date = ? AND q.status = 'done' AND s.faculty_name = ?
");
$doneStmt->execute([$today, $staff_name]);
$doneCount = $doneStmt->fetchColumn();

$totalStmt = $pdo->prepare("
    SELECT COUNT(*) FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    JOIN Services s ON a.service_id = s.id
    WHERE a.date = ? AND s.faculty_name = ?
");
$totalStmt->execute([$today, $staff_name]);
$totalToday = $totalStmt->fetchColumn();
?>

<div class="animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="row align-items-end mb-4">
        <div class="col-md-8">
            <h6 class="text-primary fw-bold text-uppercase mb-1 letter-spacing-lg">LIVE MONITOR</h6>
            <h2 class="fw-bolder mb-0">Queue Control Panel</h2>
            <p class="text-muted small mb-0"><i class="fa-solid fa-calendar-day me-2"></i> <?= date('l, F j, Y') ?> &nbsp;•&nbsp; <i class="fa-solid fa-user-tie me-2"></i> <?= htmlspecialchars($staff_name) ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
            <a href="checkin.php" class="btn btn-outline-light">
                <i class="fa-solid fa-qrcode me-2"></i> Kiosk Mode
            </a>
            <button onclick="location.reload()" class="btn btn-outline-light">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card p-4 border-0 text-center" style="background: rgba(245, 158, 11, 0.1);">
                <div class="text-warning mb-2"><i class="fa-solid fa-users-viewfinder fs-2"></i></div>
                <h2 class="fw-bolder mb-1"><?= count($waiting) ?></h2>
                <span class="small text-muted text-uppercase fw-bold">Waiting Now</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 border-0 text-center" style="background: rgba(16, 185, 129, 0.1);">
                <div class="text-success mb-2"><i class="fa-solid fa-play fs-2"></i></div>
                <h2 class="fw-bolder mb-1"><?= $current ? 1 : 0 ?></h2>
                <span class="small text-muted text-uppercase fw-bold">Active Station</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 border-0 text-center" style="background: rgba(189, 32, 49, 0.1);">
                <div class="text-primary mb-2"><i class="fa-solid fa-check-double fs-2"></i></div>
                <h2 class="fw-bolder mb-1"><?= $doneCount ?></h2>
                <span class="small text-muted text-uppercase fw-bold">Served Today</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 border-0 text-center">
                <div class="text-muted mb-2"><i class="fa-solid fa-chart-line fs-2"></i></div>
                <h2 class="fw-bolder mb-1"><?= $totalToday ?></h2>
                <span class="small text-muted text-uppercase fw-bold">Total Traffic</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Console -->
        <div class="col-lg-5">
            <!-- Active Session Card -->
            <div class="card mb-4 border-0 Modern-Glass-Card">
                <div class="card-header bg-transparent d-flex justify-content-between">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-headset text-success me-2"></i>Active Session</h5>
                    <?php if($current): ?>
                        <span class="badge bg-success bg-opacity-10 text-success">Serving</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4 text-center">
                    <?php if ($current): ?>
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 120px; height: 120px; border: 2px dashed var(--primary);">
                            <h1 class="display-3 fw-bolder text-primary mb-0">#<?= (int)$current['position'] ?></h1>
                        </div>
                        <h3 class="fw-bold mb-1 text-white"><?= htmlspecialchars($current['student_name']) ?></h3>
                        <p class="text-muted mb-4"><?= htmlspecialchars($current['service_name']) ?></p>
                        
                        <div class="d-flex gap-2">
                            <form action="manage_queue.php" method="POST" class="flex-grow-1">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="queue_id"   value="<?= (int)$current['id'] ?>">
                                <input type="hidden" name="action"     value="complete">
                                <input type="hidden" name="student_id" value="<?= (int)$current['student_id'] ?>">
                                <button type="submit" class="btn btn-success w-100 py-3 rounded-4 fw-bold">
                                    COMPLETE SERVICE <i class="fa-solid fa-check ms-2"></i>
                                </button>
                            </form>
                            <form action="manage_queue.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="queue_id"   value="<?= (int)$current['id'] ?>">
                                <input type="hidden" name="action"     value="missed">
                                <input type="hidden" name="student_id" value="<?= (int)$current['student_id'] ?>">
                                <button type="submit" class="btn btn-outline-danger py-3 px-4 rounded-4" title="Mark as Missed">
                                    <i class="fa-solid fa-user-slash"></i>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="py-5 text-muted">
                            <i class="fa-solid fa-terminal fs-1 mb-3 opacity-20"></i>
                            <h5 class="fw-bold">Ready for next</h5>
                            <p class="small mb-0">No active sessions at this moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Call Console -->
            <div class="card border-0">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-bullhorn text-warning me-2"></i>Call Next</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($waiting) && !$current): ?>
                        <?php $next = $waiting[0]; ?>
                        <div class="d-flex align-items-center mb-4 bg-white bg-opacity-5 p-3 rounded-4">
                            <div class="bg-warning rounded-pill flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <span class="fw-bold text-dark fs-4"><?= (int)$next['position'] ?></span>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold text-white"><?= htmlspecialchars($next['student_name']) ?></h6>
                                <span class="small text-muted"><?= htmlspecialchars($next['service_name']) ?></span>
                            </div>
                            <div class="ms-auto text-end">
                                <span class="small text-muted d-block">Est. Wait</span>
                                <span class="small fw-bold text-warning">READY</span>
                            </div>
                        </div>
                        <form action="manage_queue.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="queue_id"   value="<?= (int)$next['id'] ?>">
                            <input type="hidden" name="action"     value="call">
                            <input type="hidden" name="student_id" value="<?= (int)$next['student_id'] ?>">
                            <button type="submit" class="btn btn-warning w-100 py-3 rounded-4 fw-bolder text-dark">
                                CALL STUDENT #<?= (int)$next['position'] ?> <i class="fa-solid fa-volume-high ms-2"></i>
                            </button>
                        </form>
                    <?php elseif ($current): ?>
                        <div class="text-center py-3">
                            <span class="badge bg-secondary py-2 px-3 fw-medium">Busy with Current Session</span>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <p class="small mb-0">No students waiting in queue.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Waiting List -->
        <div class="col-lg-7">
            <div class="card h-100 border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list-ul text-primary me-2"></i>Waiting List</h5>
                    <span class="badge bg-primary rounded-pill"><?= count($waiting) ?> Pending</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">POS</th>
                                    <th>Student Details</th>
                                    <th>Service Type</th>
                                    <th class="text-end pe-4">Appt Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($waiting)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <p class="text-muted small">No pending students</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($waiting as $index => $w): ?>
                                        <tr class="<?= $index === 0 && !$current ? 'bg-primary bg-opacity-5' : '' ?>">
                                            <td class="ps-4">
                                                <span class="fw-bold <?= $index === 0 && !$current ? 'text-primary' : 'text-muted' ?>">#<?= (int)$w['position'] ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-white"><?= htmlspecialchars($w['student_name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($w['student_email']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-white bg-opacity-5 border border-white border-opacity-10 text-muted">
                                                    <?= htmlspecialchars($w['service_name']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="small text-muted fw-medium"><?= date('h:i A', strtotime($w['time'])) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Live Polling Logic
    const staffPolling = setInterval(async () => {
        try {
            const response = await fetch('api/queue/staff_status.php');
            const result = await response.json();
            if (result.status === 'success') {
                const data = result.data;
                const lastCount = parseInt(document.getElementById('last-waiting-count').value);
                if (data.stats.waiting_count !== lastCount) {
                    location.reload();
                }
            }
        } catch (err) {
            console.error('Polling error:', err);
        }
    }, 5000);
</script>
<input type="hidden" id="last-waiting-count" value="<?= count($waiting) ?>">

<?php require_once 'includes/footer.php'; ?>