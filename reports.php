<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';
require_once 'includes/header.php';

// Quick analytics
$total_appts = $pdo->query("SELECT count(*) FROM Appointments")->fetchColumn();
$completed_appts = $pdo->query("SELECT count(*) FROM Appointments WHERE status = 'completed'")->fetchColumn();

$stmt = $pdo->query("SELECT s.name as service_name, count(a.id) as count FROM Appointments a JOIN Services s ON a.service_id = s.id GROUP BY a.service_id");
$service_stats = $stmt->fetchAll();

$stmt = $pdo->query("SELECT date, count(*) as count FROM Appointments GROUP BY date ORDER BY date DESC LIMIT 7");
$daily_stats = $stmt->fetchAll();
?>
<div class="mt-4 mb-4 d-flex justify-content-between align-items-center">
    <h2><i class="fa-solid fa-chart-line me-2"></i> System Reports & Analytics</h2>
    <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold py-3"><i class="fa-solid fa-pie-chart text-primary me-2"></i> Overall Metrics</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 fs-5 pb-3 pt-2">
                        Total Appointments History <span class="badge bg-primary rounded-pill fs-6"><?= $total_appts ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 fs-5 pb-3 py-3">
                        Total Completed Services <span class="badge bg-success rounded-pill fs-6"><?= $completed_appts ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 fs-5 py-3 border-0">
                        Completion Rate 
                        <span class="badge bg-info text-dark rounded-pill fs-6">
                            <?= $total_appts > 0 ? round(($completed_appts/$total_appts)*100, 1) : 0 ?>%
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold py-3"><i class="fa-solid fa-ranking-star text-warning me-2"></i> Most Popular Services</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if(empty($service_stats)): ?>
                        <li class="list-group-item text-center py-4 text-muted border-0">No data available</li>
                    <?php else: ?>
                        <?php foreach($service_stats as $stat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <strong><?= htmlspecialchars($stat['service_name']) ?></strong>
                                <span class="badge bg-secondary rounded-pill"><?= $stat['count'] ?> bookings</span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white fw-bold py-3"><i class="fa-solid fa-calendar-days text-success me-2"></i> Last 7 Days Overview</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light"><tr><th class="px-4 py-3">Date</th><th class="py-3 text-end px-4">Booking Volume</th></tr></thead>
                <tbody>
                    <?php if(empty($daily_stats)): ?>
                        <tr><td colspan="2" class="text-center py-4 text-muted border-0">No data available</td></tr>
                    <?php else: ?>
                        <?php foreach($daily_stats as $day): ?>
                            <tr>
                                <td class="px-4 align-middle fw-bold"><?= date('D, M j, Y', strtotime($day['date'])) ?></td>
                                <td class="text-end px-4 align-middle">
                                    <div class="d-inline-flex align-items-center w-100 justify-content-end">
                                        <div class="progress me-3 w-50" style="height: 10px;">
                                          <div class="progress-bar" role="progressbar" style="width: <?= min(100, $day['count']*10) ?>%"></div>
                                        </div>
                                        <span class="badge bg-primary"><?= $day['count'] ?></span>
                                    </div>
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
