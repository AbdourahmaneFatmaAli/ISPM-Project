<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';
require_once 'includes/header.php';

$users = $pdo->query("SELECT count(*) FROM Users")->fetchColumn();
$appts = $pdo->query("SELECT count(*) FROM Appointments WHERE date = CURDATE()")->fetchColumn();
$services = $pdo->query("SELECT count(*) FROM Services")->fetchColumn();
?>
<div class="mt-4 mb-4">
    <h2><i class="fa-solid fa-shield-halved me-2"></i> Admin Dashboard</h2>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card shadow-md border-0 bg-primary text-white text-center p-4 hover-lift" style="background: linear-gradient(135deg, #0ea5e9, #3b82f6) !important;">
            <h3 class="fw-normal mb-3">Total Users</h3>
            <h1 class="display-4 fw-bold mb-0">
                <i class="fa-solid fa-users opacity-50 me-2 position-absolute" style="left: 20px; font-size: 80px; top: 20px;"></i>
                <?= $users ?>
            </h1>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-md border-0 bg-success text-white text-center p-4 hover-lift" style="background: linear-gradient(135deg, #10b981, #059669) !important;">
            <h3 class="fw-normal mb-3">Today's Appts</h3>
            <h1 class="display-4 fw-bold mb-0">
                <i class="fa-solid fa-calendar-check opacity-50 me-2 position-absolute" style="left: 20px; font-size: 80px; top: 20px;"></i>
                <?= $appts ?>
            </h1>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-md border-0 bg-info text-white text-center p-4 hover-lift" style="background: linear-gradient(135deg, #6366f1, #4f46e5) !important;">
            <h3 class="fw-normal mb-3">Active Services</h3>
            <h1 class="display-4 fw-bold mb-0">
                <i class="fa-solid fa-stethoscope opacity-50 me-2 position-absolute" style="left: 20px; font-size: 80px; top: 20px;"></i>
                <?= $services ?>
            </h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-md border-0 hover-lift">
            <div class="card-header bg-white py-3 fw-bold">Admin Actions</div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="manage_users.php" class="btn btn-outline-primary btn-lg text-start"><i class="fa-solid fa-users me-3 text-secondary"></i> Manage Users</a>
                    <a href="manage_services.php" class="btn btn-outline-success btn-lg text-start"><i class="fa-solid fa-stethoscope me-3 text-secondary"></i> Manage Services</a>
                    <a href="reports.php" class="btn btn-outline-dark btn-lg text-start"><i class="fa-solid fa-chart-line me-3 text-secondary"></i> View Reports</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-md border-0 hover-lift">
            <div class="card-header bg-white py-3 fw-bold">Recent System Activity</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="px-3">Time</th><th>Action</th></tr></thead>
                    <tbody>
                        <tr><td class="px-3 text-muted">Just now</td><td>Admin accessed dashboard</td></tr>
                        <tr><td class="px-3 text-muted">1 hr ago</td><td>New appointment booked (System)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
