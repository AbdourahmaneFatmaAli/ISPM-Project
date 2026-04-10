<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    if(isset($_POST['add_service'])) {
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        $faculty = trim($_POST['faculty_name']);
        $building = trim($_POST['building']);
        
        if(!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO Services (name, description, faculty_name, building) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $desc, $faculty, $building]);
        }
    } elseif(isset($_POST['update_service'])) {
        $id = $_POST['service_id'];
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        $faculty = trim($_POST['faculty_name']);
        $building = trim($_POST['building']);
        
        if(!empty($name)) {
            $stmt = $pdo->prepare("UPDATE Services SET name = ?, description = ?, faculty_name = ?, building = ? WHERE id = ?");
            $stmt->execute([$name, $desc, $faculty, $building, $id]);
        }
    } elseif(isset($_POST['delete_service'])) {
        $id = $_POST['service_id'];
        $stmt = $pdo->prepare("DELETE FROM Services WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: manage_services.php");
    exit;
}

require_once 'includes/header.php';
$services = $pdo->query("SELECT * FROM Services ORDER BY name")->fetchAll();
?>
<div class="mt-4 mb-4 d-flex justify-content-between align-items-center">
    <h2><i class="fa-solid fa-stethoscope me-2"></i> Manage Services</h2>
    <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">Add New Service</h5></div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="add_service" value="1">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Service Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Admissions">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Staff/Faculty Name</label>
                        <input type="text" name="faculty_name" class="form-control" placeholder="e.g. John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Building/Room</label>
                        <input type="text" name="building" class="form-control" placeholder="e.g. Room 101, Main Building">
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold mb-1">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Describe the service..."></textarea>
                    </div>
                    <button class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-plus me-2"></i> Add Service</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">Service</th>
                            <th class="py-3">Staff & Location</th>
                            <th class="py-3">Description</th>
                            <th class="text-end px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($services as $s): ?>
                            <tr>
                                <td class="px-4 align-middle fw-bold"><?= e($s['name']) ?></td>
                                <td class="align-middle">
                                    <div class="fw-bold text-primary"><?= e($s['faculty_name'] ?? 'Staff') ?></div>
                                    <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i><?= e($s['building'] ?? 'Main Building') ?></small>
                                </td>
                                <td class="align-middle"><?= e($s['description']) ?></td>
                                <td class="text-end px-4 align-middle">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editService<?= $s['id'] ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="" method="POST" onsubmit="return confirm('Deleting this service will remove all associated appointments. Are you sure?');">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="delete_service" value="1">
                                            <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>

                                    <!-- Edit Service Modal -->
                                    <div class="modal fade" id="editService<?= $s['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content border-0 shadow-lg">
                                                <form action="" method="POST">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold">Edit Service</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body py-4">
                                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                        <input type="hidden" name="update_service" value="1">
                                                        <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                                        
                                                        <div class="mb-3 text-start">
                                                            <label class="form-label fw-bold">Service Name</label>
                                                            <input type="text" name="name" class="form-control" value="<?= e($s['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3 text-start">
                                                            <label class="form-label fw-bold">Staff/Faculty Name</label>
                                                            <input type="text" name="faculty_name" class="form-control" value="<?= e($s['faculty_name']) ?>">
                                                        </div>
                                                        <div class="mb-3 text-start">
                                                            <label class="form-label fw-bold">Building/Room</label>
                                                            <input type="text" name="building" class="form-control" value="<?= e($s['building']) ?>">
                                                        </div>
                                                        <div class="mb-0 text-start">
                                                            <label class="form-label fw-bold">Description</label>
                                                            <textarea name="description" class="form-control" rows="3"><?= e($s['description']) ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary fw-bold px-4">Update Service</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($services)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No services found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
