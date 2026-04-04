<?php
require_once 'includes/auth_check.php';
require_role('staff');
require_once 'config/database.php';
require_once 'includes/header.php';

$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT q.*, a.time, s.name as service_name, u.name as student_name, u.id as student_id
    FROM Queue q
    JOIN Appointments a ON q.appointment_id = a.id
    JOIN Services s ON a.service_id = s.id
    JOIN Users u ON a.user_id = u.id
    WHERE a.date = ? AND q.status != 'done'
    ORDER BY q.position ASC
");
$stmt->execute([$today]);
$queue = $stmt->fetchAll();

$serving = array_filter($queue, fn($v) => $v['status'] == 'serving');
$waiting = array_filter($queue, fn($v) => $v['status'] == 'waiting');
?>
<div class="d-flex justify-content-between align-items-center mt-4 mb-4">
    <h2><i class="fa-solid fa-desktop me-2"></i> Queue Monitor</h2>
    <div>
        <a href="checkin.php" class="btn btn-outline-info"><i class="fa-solid fa-qrcode me-2"></i> Kiosk Mode</a>
        <a href="staff_dashboard.php" class="btn btn-primary"><i class="fa-solid fa-arrows-rotate me-2"></i> Refresh</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-md border-0 border-top border-success border-4 mb-4 hover-lift">
            <div class="card-header bg-white text-center py-3">
                <h4 class="mb-0 text-success">Currently Serving</h4>
            </div>
            <div class="card-body text-center p-4">
                <?php if($serving): ?>
                    <?php $current = current($serving); ?>
                    <h1 class="display-1 fw-bold"><?= $current['position'] ?></h1>
                    <h4 class="mt-3"><?= htmlspecialchars($current['student_name']) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($current['service_name']) ?></p>
                    <hr>
                    <form action="manage_queue.php" method="POST">
                        <input type="hidden" name="queue_id" value="<?= $current['id'] ?>">
                        <input type="hidden" name="action" value="complete">
                        <input type="hidden" name="student_id" value="<?= $current['student_id'] ?>">
                        <button type="submit" class="btn btn-success btn-lg w-100"><i class="fa-solid fa-check-double me-2"></i> Mark Completed</button>
                    </form>
                <?php else: ?>
                    <div class="py-5">
                        <i class="fa-solid fa-mug-hot fa-3x text-muted mb-3"></i>
                        <h5>No active session</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-md border-0 border-top border-warning border-4 hover-lift">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0 text-warning">Waiting List</h4>
                <span class="badge bg-warning text-dark fs-6"><?= count($waiting) ?> waiting</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Pos</th>
                                <th>Student</th>
                                <th>Service</th>
                                <th>Time</th>
                                <th class="text-end px-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($waiting)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">The queue is currently empty.</td></tr>
                            <?php else: ?>
                                <?php foreach($waiting as $index => $w): ?>
                                    <tr>
                                        <td class="px-4 fw-bold fs-5 align-middle"><?= $w['position'] ?></td>
                                        <td class="align-middle"><?= htmlspecialchars($w['student_name']) ?></td>
                                        <td class="align-middle"><?= htmlspecialchars($w['service_name']) ?></td>
                                        <td class="align-middle"><?= $w['time'] ?></td>
                                        <td class="text-end px-4 align-middle">
                                            <?php if(!$serving && $index === array_key_first($waiting)): ?>
                                                <form action="manage_queue.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="queue_id" value="<?= $w['id'] ?>">
                                                    <input type="hidden" name="action" value="call">
                                                    <input type="hidden" name="student_id" value="<?= $w['student_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning fw-bold px-3">Call Next <i class="fa-solid fa-arrow-right ms-1"></i></button>
                                                </form>
                                            <?php endif; ?>
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
<?php require_once 'includes/footer.php'; ?>
