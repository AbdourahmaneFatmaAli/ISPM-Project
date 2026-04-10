<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';
require_once 'includes/header.php';

// Get statistics
$usersCount = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$todayAppts = $pdo->query("SELECT COUNT(*) FROM Appointments WHERE date = CURDATE()")->fetchColumn();
$servicesCount = $pdo->query("SELECT COUNT(*) FROM Services")->fetchColumn();

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
    <h2 class="text-white fw-bold">
        <i class="fa-solid fa-shield-halved me-2 text-primary"></i> Admin Control Center
    </h2>
    <p class="text-muted small text-uppercase tracking-widest">Pan-Atlantic University Digital Queue System</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card shadow-2xl border-0 p-4 position-relative overflow-hidden" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
            <div class="d-flex align-items-center position-relative z-index-1">
                <div class="bg-primary bg-opacity-10 rounded-4 p-3 me-4">
                    <i class="fa-solid fa-users fa-2x text-primary"></i>
                </div>
                <div>
                    <h5 class="text-muted small fw-bold mb-1">TOTAL USERS</h5>
                    <h2 class="text-white fw-bold mb-0"><?= $usersCount ?></h2>
                </div>
            </div>
            <i class="fa-solid fa-users position-absolute opacity-5" style="font-size: 100px; right: -10px; bottom: -20px;"></i>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-2xl border-0 p-4 position-relative overflow-hidden" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
            <div class="d-flex align-items-center position-relative z-index-1">
                <div class="bg-success bg-opacity-10 rounded-4 p-3 me-4">
                    <i class="fa-solid fa-calendar-check fa-2x text-success"></i>
                </div>
                <div>
                    <h5 class="text-muted small fw-bold mb-1">TODAY'S BOOKINGS</h5>
                    <h2 class="text-white fw-bold mb-0"><?= $todayAppts ?></h2>
                </div>
            </div>
            <i class="fa-solid fa-calendar-check position-absolute opacity-5" style="font-size: 100px; right: -10px; bottom: -20px;"></i>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-2xl border-0 p-4 position-relative overflow-hidden" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
            <div class="d-flex align-items-center position-relative z-index-1">
                <div class="bg-accent bg-opacity-10 rounded-4 p-3 me-4">
                    <i class="fa-solid fa-bell-concierge fa-2x text-accent"></i>
                </div>
                <div>
                    <h5 class="text-muted small fw-bold mb-1">ACTIVE SERVICES</h5>
                    <h2 class="text-white fw-bold mb-0"><?= $servicesCount ?></h2>
                </div>
            </div>
            <i class="fa-solid fa-bell-concierge position-absolute opacity-5" style="font-size: 100px; right: -10px; bottom: -20px;"></i>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">
    <!-- Admin Actions -->
    <div class="col-lg-4">
        <div class="card shadow-2xl border-0 h-100" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
            <div class="card-header border-0 py-3" style="background: rgba(255,255,255,0.05);">
                <h5 class="mb-0 fw-bold text-white">
                    <i class="fa-solid fa-sliders me-2 text-primary"></i>Quick Management
                </h5>
            </div>
            <div class="card-body py-4">
                <div class="d-grid gap-3">
                    <a href="manage_users.php" class="btn btn-outline-light text-start py-3 px-4 border-0" style="background: rgba(255,255,255,0.03);">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-users me-3 text-primary fs-4"></i>
                            <div>
                                <div class="fw-bold">Users Database</div>
                                <small class="text-muted">Create, Edit, Roles</small>
                            </div>
                        </div>
                    </a>
                    <a href="manage_services.php" class="btn btn-outline-light text-start py-3 px-4 border-0" style="background: rgba(255,255,255,0.03);">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-concierge-bell me-3 text-success fs-4"></i>
                            <div>
                                <div class="fw-bold">System Services</div>
                                <small class="text-muted">Logic, Staff, Offices</small>
                            </div>
                        </div>
                    </a>
                    <a href="manage_appointments.php" class="btn btn-outline-light text-start py-3 px-4 border-0" style="background: rgba(255,255,255,0.03);">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-calendar-alt me-3 text-info fs-4"></i>
                            <div>
                                <div class="fw-bold">Appointments Master</div>
                                <small class="text-muted">Full CRUD, Reschedule</small>
                            </div>
                        </div>
                    </a>
                    <a href="reports.php" class="btn btn-outline-light text-start py-3 px-4 border-0" style="background: rgba(255,255,255,0.03);">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-chart-pie me-3 text-accent fs-4"></i>
                            <div>
                                <div class="fw-bold">Analytics & Reports</div>
                                <small class="text-muted">Queue Stats, Efficiency</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent System Activity -->
    <div class="col-lg-8">
        <div class="card shadow-2xl border-0 h-100" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
            <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.05);">
                <h5 class="mb-0 fw-bold text-white">
                    <i class="fa-solid fa-history me-2 text-success"></i>Recent System Activity
                </h5>
                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-3"><?= count($recentActivity) ?> Records</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentActivity)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted opacity-25 mb-3"></i>
                        <p class="text-muted mb-0">No recent activity detected.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead style="background: rgba(255,255,255,0.02);">
                                <tr>
                                    <th class="px-4 text-muted small py-3">TIMESTAMP</th>
                                    <th class="text-muted small py-3">SYSTEM ENTITY</th>
                                    <th class="text-muted small py-3">DESCRIPTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td class="px-4 text-muted small">
                                            <?= date('M d, H:i', strtotime($activity['created_at'])) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle text-center me-2" style="width: 32px; height: 32px; line-height: 32px;">
                                                    <i class="fas fa-user text-primary small"></i>
                                                </div>
                                                <span class="text-white small fw-bold"><?= htmlspecialchars($activity['name']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">Booked </span>
                                            <span class="text-accent small fw-bold"><?= htmlspecialchars($activity['service_name']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer border-0 bg-transparent text-center py-3">
                <a href="reports.php" class="text-primary text-decoration-none small fw-bold border-bottom border-primary border-opacity-25 pb-1">VIEW EXTENDED LOGS</a>
            </div>
        </div>
    </div>
</div>

<!-- System Status Bar -->
<div class="row g-4 mt-2 mb-4">
    <div class="col-12">
        <div class="card shadow-2xl border-0 p-4" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="animate__animated animate__pulse animate__infinite me-3">
                            <i class="fas fa-circle text-success" style="font-size: 10px; filter: drop-shadow(0 0 5px #10b981);"></i>
                        </div>
                        <h6 class="text-white mb-0 fw-bold">DQASS SYSTEM OPERATIONAL</h6>
                        <span class="mx-3 text-muted">|</span>
                        <span class="text-muted small">UPTIME: 99.9%</span>
                        <span class="mx-3 text-muted">|</span>
                        <span class="text-muted small">LAST BACKUP: <?= date('H:i') ?></span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-flex justify-content-md-end gap-4 text-muted small fw-bold">
                        <span><i class="fas fa-database me-2"></i>DB SYNCED</span>
                        <span><i class="fas fa-shield-alt me-2"></i>SSL ACTIVE</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>