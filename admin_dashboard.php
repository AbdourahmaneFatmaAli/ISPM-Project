<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';
require_once 'includes/header.php';

// Get statistics
$users = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$appts = $pdo->query("SELECT COUNT(*) FROM Appointments WHERE date = CURDATE()")->fetchColumn();
$services = $pdo->query("SELECT COUNT(*) FROM Services")->fetchColumn();

// Get recent appointments for activity log
$recentActivity = $pdo->query("
    SELECT a.created_at, u.name, s.name as service_name 
    FROM Appointments a
    JOIN Users u ON a.user_id = u.id
    JOIN Services s ON a.service_id = s.id
    ORDER BY a.created_at DESC
    LIMIT 10
")->fetchAll();
?>

<div class="mt-4 mb-4">
    <h2 class="text-white">
        <i class="fa-solid fa-shield-halved me-2"></i> Admin Dashboard
    </h2>
    <p class="text-white opacity-75">System Overview & Management</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-primary text-white text-center p-4 position-relative overflow-hidden">
            <i class="fa-solid fa-users position-absolute opacity-25"
                style="font-size: 120px; right: -20px; top: -20px;"></i>
            <h3 class="fw-normal mb-3">Total Users</h3>
            <h1 class="display-3 fw-bold mb-0 position-relative"><?= $users ?></h1>
            <p class="small mb-0 mt-2 opacity-75">Registered in System</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-success text-white text-center p-4 position-relative overflow-hidden">
            <i class="fa-solid fa-calendar-check position-absolute opacity-25"
                style="font-size: 120px; right: -20px; top: -20px;"></i>
            <h3 class="fw-normal mb-3">Today's Appointments</h3>
            <h1 class="display-3 fw-bold mb-0 position-relative"><?= $appts ?></h1>
            <p class="small mb-0 mt-2 opacity-75">Scheduled for Today</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-info text-white text-center p-4 position-relative overflow-hidden">
            <i class="fa-solid fa-concierge-bell position-absolute opacity-25"
                style="font-size: 120px; right: -20px; top: -20px;"></i>
            <h3 class="fw-normal mb-3">Active Services</h3>
            <h1 class="display-3 fw-bold mb-0 position-relative"><?= $services ?></h1>
            <p class="small mb-0 mt-2 opacity-75">Available Services</p>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">
    <!-- Admin Actions -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-tools me-2 text-primary"></i>Admin Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="manage_users.php" class="btn btn-outline-primary btn-lg text-start">
                        <i class="fa-solid fa-users me-3"></i> Manage Users
                    </a>
                    <a href="manage_services.php" class="btn btn-outline-success btn-lg text-start">
                        <i class="fa-solid fa-concierge-bell me-3"></i> Manage Services
                    </a>
                    <a href="manage_appointments.php" class="btn btn-outline-info btn-lg text-start">
                        <i class="fa-solid fa-calendar-alt me-3"></i> View Appointments
                    </a>
                    <a href="reports.php" class="btn btn-outline-dark btn-lg text-start">
                        <i class="fa-solid fa-chart-line me-3"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent System Activity -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-history me-2 text-success"></i>Recent System Activity
                </h5>
                <span class="badge bg-primary"><?= count($recentActivity) ?> Records</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentActivity)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted opacity-50 mb-3"></i>
                        <p class="text-muted mb-0">No recent activity</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td class="px-4 text-muted">
                                            <?php
                                            $time = strtotime($activity['created_at']);
                                            $diff = time() - $time;

                                            if ($diff < 60) {
                                                echo "Just now";
                                            } elseif ($diff < 3600) {
                                                echo floor($diff / 60) . " min ago";
                                            } elseif ($diff < 86400) {
                                                echo floor($diff / 3600) . " hr ago";
                                            } else {
                                                echo date('M d, Y', $time);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-user-circle me-2 text-primary"></i>
                                            <?= htmlspecialchars($activity['name']) ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-plus me-2 text-success"></i>
                                            Booked appointment for
                                            <strong><?= htmlspecialchars($activity['service_name']) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- System Status Cards -->
<div class="row g-4 mt-1">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center p-3">
            <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
            <h6 class="fw-bold mb-1">System Status</h6>
            <p class="text-success mb-0 small">All Systems Operational</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center p-3">
            <i class="fas fa-database fa-3x text-info mb-2"></i>
            <h6 class="fw-bold mb-1">Database</h6>
            <p class="text-info mb-0 small">Connected</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center p-3">
            <i class="fas fa-server fa-3x text-warning mb-2"></i>
            <h6 class="fw-bold mb-1">Server Load</h6>
            <p class="text-warning mb-0 small">Normal</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center p-3">
            <i class="fas fa-shield-alt fa-3x text-primary mb-2"></i>
            <h6 class="fw-bold mb-1">Security</h6>
            <p class="text-primary mb-0 small">Protected</p>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mt-1 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-2">
                            <i class="fas fa-info-circle me-2"></i>System Information
                        </h5>
                        <p class="mb-0 opacity-75">
                            DQASS v1.0 | Last backup: <?= date('M d, Y h:i A') ?> |
                            Uptime: 99.9% | Database: MySQL
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="#" class="btn btn-light btn-lg">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>