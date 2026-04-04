<?php 
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php'; 

$stmt = $pdo->prepare("SELECT * FROM Notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>
<div class="dashboard-header d-flex justify-content-between align-items-center mt-4">
    <h2><i class="fa-solid fa-gauge me-2"></i> Student Dashboard</h2>
    <a href="book.php" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Book Appointment</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-md border-0 mb-4 hover-lift">
            <div class="card-body">
                <h4 class="card-title mb-4">Quick Actions</h4>
                <div class="d-grid gap-3 d-md-flex">
                    <a href="my_appointments.php" class="btn btn-outline-primary btn-lg"><i class="fa-regular fa-calendar-lines me-2"></i>View My Appointments</a>
                    <a href="notifications.php" class="btn btn-outline-info btn-lg"><i class="fa-regular fa-bell me-2"></i>All Notifications</a>
                </div>
            </div>
        </div>
        
        <div class="card shadow-md border-0 hover-lift">
            <div class="card-header bg-white">
                <h5 class="mb-0">Upcoming Appointment</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $pdo->prepare("SELECT a.*, s.name as service_name FROM Appointments a JOIN Services s ON a.service_id = s.id WHERE a.user_id = ? AND a.status = 'booked' ORDER BY a.date ASC, a.time ASC LIMIT 1");
                $stmt->execute([$_SESSION['user_id']]);
                $upcoming = $stmt->fetch();
                
                if($upcoming): ?>
                    <div class="alert alert-primary">
                        <i class="fa-solid fa-calendar-day me-2"></i> <strong><?= htmlspecialchars($upcoming['service_name']) ?></strong> on <strong><?= $upcoming['date'] ?></strong> at <strong><?= $upcoming['time'] ?></strong>
                        <hr>
                        <a href="qr.php?id=<?= $upcoming['id'] ?>" class="btn btn-sm btn-success"><i class="fa-solid fa-qrcode me-1"></i> View Check-in QR</a>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">You have no upcoming appointments.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-md border-0 hover-lift">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Notifications</h5>
                <span class="badge bg-danger"><?= count($notifications) ?></span>
            </div>
            <ul class="list-group list-group-flush">
                <?php if(empty($notifications)): ?>
                    <li class="list-group-item text-muted text-center py-4">No new notifications.</li>
                <?php else: ?>
                    <?php foreach($notifications as $notif): ?>
                        <li class="list-group-item <?= $notif['status'] == 'unread' ? 'fw-bold bg-light' : '' ?>">
                            <p class="mb-1"><i class="fa-regular fa-envelope me-2 text-primary"></i><?= htmlspecialchars($notif['message']) ?></p>
                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= date('M j, Y g:i A', strtotime($notif['created_at'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <div class="card-footer text-center bg-white">
                <a href="notifications.php" class="text-decoration-none">View All Notifications</a>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
