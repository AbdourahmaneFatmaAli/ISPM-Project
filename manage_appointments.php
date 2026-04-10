<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    if(isset($_POST['add_appointment'])) {
        $user_id = $_POST['student_id'];
        $service_id = $_POST['service_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $qr = "APPT-" . uniqid() . "-USR-" . $user_id;
        
        $stmt = $pdo->prepare("INSERT INTO Appointments (user_id, service_id, date, time, status, qr_code) VALUES (?, ?, ?, ?, 'booked', ?)");
        $stmt->execute([$user_id, $service_id, $date, $time, $qr]);
        $success = "Appointment booked successfully!";
    } elseif(isset($_POST['update_appointment'])) {
        $id = $_POST['appt_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE Appointments SET date = ?, time = ?, status = ? WHERE id = ?");
        $stmt->execute([$date, $time, $status, $id]);
        $success = "Appointment updated successfully!";
    } elseif(isset($_POST['delete_appointment'])) {
        $id = $_POST['appt_id'];
        $pdo->prepare("DELETE FROM Appointments WHERE id = ?")->execute([$id]);
        $success = "Appointment removed successfully!";
    }
    
    if(isset($success)) header("Location: manage_appointments.php?success=" . urlencode($success));
    else header("Location: manage_appointments.php");
    exit;
}

require_once 'includes/header.php';

// Fetch appointments with details
$appts = $pdo->query("
    SELECT a.*, u.name as user_name, s.name as service_name 
    FROM Appointments a
    JOIN Users u ON a.user_id = u.id
    JOIN Services s ON a.service_id = s.id
    ORDER BY a.date DESC, a.time DESC
")->fetchAll();

// Fetch students and services for adding new appt
$students = $pdo->query("SELECT id, name FROM Users WHERE role = 'student' ORDER BY name")->fetchAll();
$services = $pdo->query("SELECT id, name FROM Services ORDER BY name")->fetchAll();
?>

<div class="row align-items-center mt-4 mb-4 g-3">
    <div class="col-md-6">
        <h2 class="text-white mb-0"><i class="fa-solid fa-calendar-check me-2 text-primary"></i> System Appointments</h2>
    </div>
    <div class="col-md-6 text-md-end">
        <button class="btn btn-primary px-4 fw-bold me-2" data-bs-toggle="modal" data-bs-target="#addApptModal">
            <i class="fa-solid fa-calendar-plus me-2"></i> Book Appointment
        </button>
        <a href="admin_dashboard.php" class="btn btn-outline-light">
            <i class="fa-solid fa-arrow-left me-2"></i> Dashboard
        </a>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm animate__animated animate__fadeIn"><?= e($_GET['success']) ?></div>
<?php endif; ?>

<div class="card shadow-2xl border-0 overflow-hidden" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th class="px-4 py-3 text-muted small">STUDENT</th>
                    <th class="py-3 text-muted small">SERVICE</th>
                    <th class="py-3 text-muted small">SCHEDULE</th>
                    <th class="py-3 text-muted small">STATUS</th>
                    <th class="text-end px-4 py-3 text-muted small">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($appts as $a): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-white"><?= e($a['user_name']) ?></div>
                            <div class="small text-muted">ID: #<?= e($a['user_id']) ?></div>
                        </td>
                        <td class="py-3">
                            <div class="text-white"><?= e($a['service_name']) ?></div>
                        </td>
                        <td class="py-3">
                            <div class="text-white small fw-bold"><i class="fa-solid fa-calendar me-2 text-primary"></i><?= e($a['date']) ?></div>
                            <div class="text-muted small"><i class="fa-solid fa-clock me-2 text-primary"></i><?= e(date('h:i A', strtotime($a['time']))) ?></div>
                        </td>
                        <td class="py-3">
                            <span class="badge rounded-pill <?= 
                                $a['status'] == 'completed' ? 'bg-success' : 
                                ($a['status'] == 'missed' ? 'bg-danger' : 
                                ($a['status'] == 'checked-in' ? 'bg-info' : 'bg-primary')) 
                            ?> text-white" style="font-size: 0.75rem; padding: 0.4em 1em;">
                                <?= strtoupper(e($a['status'])) ?>
                            </span>
                        </td>
                        <td class="text-end px-4 py-3">
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-outline-primary border-0 hover-lift" 
                                        data-bs-toggle="modal" data-bs-target="#editApptModal<?= $a['id'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="" method="POST" onsubmit="return confirm('Cancel this appointment permanently?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="delete_appointment" value="1">
                                    <input type="hidden" name="appt_id" value="<?= $a['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger border-0 hover-lift">
                                        <i class="fa-solid fa-calendar-xmark"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Edit Appointment Modal -->
                            <div class="modal fade" id="editApptModal<?= $a['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content text-start" style="background: var(--bg-surface); border: 1px solid var(--glass-border);">
                                        <form action="" method="POST">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title fw-bold text-white">Reschedule / Update</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body py-4">
                                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                <input type="hidden" name="update_appointment" value="1">
                                                <input type="hidden" name="appt_id" value="<?= $a['id'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">DATE</label>
                                                    <input type="date" name="date" class="form-control" value="<?= e($a['date']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">TIME</label>
                                                    <input type="time" name="time" class="form-control" value="<?= e($a['time']) ?>" required>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label text-muted small fw-bold">STATUS</label>
                                                    <select name="status" class="form-select">
                                                        <option value="booked" <?= $a['status']=='booked'?'selected':'' ?>>Booked</option>
                                                        <option value="checked-in" <?= $a['status']=='checked-in'?'selected':'' ?>>Checked In</option>
                                                        <option value="completed" <?= $a['status']=='completed'?'selected':'' ?>>Completed</option>
                                                        <option value="missed" <?= $a['status']=='missed'?'selected':'' ?>>Missed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($appts)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fa-solid fa-calendar-xmark fa-3x d-block mb-3 opacity-25"></i>No appointments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addApptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--glass-border);">
            <form action="" method="POST">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-white">Manual Appointment Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="add_appointment" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">SELECT STUDENT</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Choose a student...</option>
                            <?php foreach($students as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">SELECT SERVICE</label>
                        <select name="service_id" class="form-select" required>
                            <option value="">Choose a service...</option>
                            <?php foreach($services as $sv): ?>
                                <option value="<?= $sv['id'] ?>"><?= e($sv['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small fw-bold">DATE</label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small fw-bold">TIME</label>
                            <input type="time" name="time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Book Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
