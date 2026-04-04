<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php';

$stmt = $pdo->prepare("SELECT a.*, s.name as service_name FROM Appointments a JOIN Services s ON a.service_id = s.id WHERE a.user_id = ? ORDER BY a.date DESC, a.time DESC");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mt-4 mb-4">
    <h2><i class="fa-regular fa-calendar-lines me-2"></i> My Appointments</h2>
    <a href="book.php" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Book New</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">Service</th>
                        <th class="py-3">Date & Time</th>
                        <th class="py-3">Status</th>
                        <th class="text-end px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($appointments)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">You have no appointments.</td></tr>
                    <?php else: ?>
                        <?php foreach($appointments as $appt): ?>
                            <tr>
                                <td class="px-4 align-middle fw-bold"><?= htmlspecialchars($appt['service_name']) ?></td>
                                <td class="align-middle">
                                    <i class="fa-regular fa-calendar text-muted me-1"></i> <?= $appt['date'] ?> <br>
                                    <i class="fa-regular fa-clock text-muted me-1"></i> <?= date('h:i A', strtotime($appt['time'])) ?>
                                </td>
                                <td class="align-middle">
                                    <?php if($appt['status'] == 'booked'): ?>
                                        <span class="badge bg-primary">Booked</span>
                                    <?php elseif($appt['status'] == 'checked-in'): ?>
                                        <span class="badge bg-success">Checked-in</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end px-4 align-middle">
                                    <?php if($appt['status'] == 'booked'): ?>
                                        <a href="qr.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-qrcode me-1"></i> View QR</a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light" disabled>QR Expired</button>
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
<?php require_once 'includes/footer.php'; ?>
