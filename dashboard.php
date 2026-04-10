<?php 
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php'; 

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM Notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Fetch upcoming appointment with live queue info if available
$u_stmt = $pdo->prepare("
    SELECT a.*, s.name as service_name, q.position as live_pos, q.status as q_status
    FROM Appointments a 
    JOIN Services s ON a.service_id = s.id 
    LEFT JOIN Queue q ON a.id = q.appointment_id
    WHERE a.user_id = ? AND a.status IN ('booked', 'checked-in') 
    ORDER BY a.date ASC, a.time ASC LIMIT 1
");
$u_stmt->execute([$_SESSION['user_id']]);
$upcoming = $u_stmt->fetch();
?>

<div class="row animate__animated animate__fadeIn">
    <div class="col-12 mb-4 d-flex justify-content-between align-items-end">
        <div>
            <h6 class="text-primary fw-bold mb-1">WELCOME BACK</h6>
            <h2 class="fw-bolder mb-0">Student Dashboard</h2>
        </div>
        <a href="book.php" class="btn btn-primary d-none d-md-flex">
            <i class="fa-solid fa-calendar-plus me-2"></i> New Appointment
        </a>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: linear-gradient(135deg, rgba(189, 32, 49, 0.15), var(--bg-surface));">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-3 p-3 me-3 text-white">
                        <i class="fa-solid fa-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-white">3</h4>
                        <span class="small text-muted">Total Bookings</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-0" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), var(--bg-surface));">
                <div class="d-flex align-items-center">
                    <div class="bg-success rounded-3 p-3 me-3 text-white">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-white">12</h4>
                        <span class="small text-muted">Services Done</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Active/Upcoming Appointment Card -->
            <div class="card mb-4 border-0 Modern-Glass-Card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-bullseye text-primary me-2"></i>Active Appointment</h5>
                    <?php if($upcoming && $upcoming['status'] == 'checked-in'): ?>
                        <span class="badge bg-success animate__animated animate__pulse animate__infinite">Live in Queue</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <?php if($upcoming): ?>
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h3 class="fw-bold text-white mb-2"><?= e($upcoming['service_name']) ?></h3>
                                <div class="d-flex gap-3 text-muted mb-4">
                                    <span><i class="fa-solid fa-calendar me-1 text-primary"></i> <?= e($upcoming['date']) ?></span>
                                    <span><i class="fa-solid fa-clock me-1 text-primary"></i> <?= e($upcoming['time']) ?></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="qr.php?id=<?= e($upcoming['id']) ?>" class="btn btn-primary px-4">
                                        Check In <i class="fa-solid fa-qrcode ms-2"></i>
                                    </a>
                                    <a href="my_appointments.php" class="btn btn-outline-light">Details</a>
                                </div>
                            </div>
                            <div class="col-md-5 mt-4 mt-md-0 text-center border-start border-white border-opacity-10">
                                <?php if($upcoming['live_pos'] !== null): ?>
                                    <div class="display-4 fw-bolder text-primary mb-0">#<?= e($upcoming['live_pos']) ?></div>
                                    <div class="text-muted small text-uppercase fw-bold letter-spacing-sm">Current Position</div>
                                <?php else: ?>
                                    <div class="bg-white bg-opacity-5 p-4 rounded-4">
                                        <i class="fa-solid fa-hourglass-start fs-2 text-muted mb-3 d-block"></i>
                                        <span class="text-muted small">Not checked in yet</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-calendar-xmark text-muted fs-1 mb-3 d-block"></i>
                            <p class="text-muted">No upcoming appointments found.</p>
                            <a href="book.php" class="btn btn-primary">Book Your First Service</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Access Grid -->
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="my_appointments.php" class="text-decoration-none">
                        <div class="card p-4 h-100 bg-surface border-0 hover-lift">
                            <i class="fa-solid fa-list-check text-primary fs-3 mb-3"></i>
                            <h5 class="fw-bold text-white">My Bookings</h5>
                            <p class="text-muted small mb-0">View history and manage upcoming slots.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="notifications.php" class="text-decoration-none">
                        <div class="card p-4 h-100 bg-surface border-0 hover-lift">
                            <i class="fa-solid fa-bell text-warning fs-3 mb-3"></i>
                            <h5 class="fw-bold text-white">Alerts</h5>
                            <p class="text-muted small mb-0">Check arrival notices and missed calls.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar: Notifications -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card h-100 border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Alerts</h5>
                    <a href="notifications.php" class="small text-primary text-decoration-none fw-bold">Clear All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if(empty($notifications)): ?>
                            <div class="p-5 text-center text-muted">
                                <i class="fa-solid fa-envelope-open fs-2 mb-3 d-block"></i>
                                Inbox empty
                            </div>
                        <?php else: ?>
                            <?php foreach($notifications as $notif): ?>
                                <div class="list-group-item p-3 border-white border-opacity-5 bg-transparent">
                                    <div class="d-flex w-100 justify-content-between mb-1">
                                        <h6 class="mb-0 fw-bold small <?= $notif['status'] == 'unread' ? 'text-primary' : 'text-white' ?>">
                                            <i class="fa-solid <?= $notif['status'] == 'unread' ? 'fa-circle-dot animate__animated animate__pulse animate__infinite' : 'fa-circle' ?> me-2 small"></i>
                                            <?= e(substr($notif['message'], 0, 30)) ?>...
                                        </h6>
                                        <small class="text-muted"><?= date('H:i', strtotime($notif['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1 text-muted small"><?= e(substr($notif['message'], 0, 80)) ?>...</p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent py-3 text-center border-white border-opacity-5">
                    <a href="notifications.php" class="btn btn-outline-light btn-sm w-100">View Full Inbox</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
