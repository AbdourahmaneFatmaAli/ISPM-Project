<?php 
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php'; 

$stmt = $pdo->query("SELECT * FROM Services");
$services = $stmt->fetchAll();
?>
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="mb-0"><i class="fa-solid fa-calendar-plus me-2"></i> Book an Appointment</h4>
            </div>
            <div class="card-body p-4">
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success fw-bold"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($_GET['success']) ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger fw-bold"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                
                <form action="api/appointments/create.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Service</label>
                        <select name="service_id" class="form-select form-select-lg" required>
                            <option value="">-- Choose a Service --</option>
                            <?php foreach($services as $service): ?>
                                <option value="<?= $service['id'] ?>">
                                    <?= htmlspecialchars($service['name']) ?> - 
                                    Staff: <?= htmlspecialchars($service['faculty_name'] ?: 'Staff') ?> | 
                                    Location: <?= htmlspecialchars($service['building'] ?: 'Main Building') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-2 d-block">
                            <i class="fa-solid fa-info-circle"></i> 
                            Select a service to see which staff member will assist you and where to go.
                        </small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" name="date" class="form-control form-control-lg" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Time</label>
                            <input type="time" name="time" class="form-control form-control-lg" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa-solid fa-clock me-2"></i>
                        <strong>Important:</strong> Please arrive 10 minutes before your scheduled time. 
                        You will receive a notification when it's your turn.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="fa-solid fa-check me-2"></i> Confirm Booking
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .form-select-lg, .form-control-lg {
        padding: 12px 16px;
        font-size: 1rem;
    }
    select option {
        padding: 10px;
    }
</style>

<?php require_once 'includes/footer.php'; ?>