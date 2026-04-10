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
            $success = "Service added successfully!";
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
            $success = "Service updated successfully!";
        }
    } elseif(isset($_POST['delete_service'])) {
        $id = $_POST['service_id'];
        $stmt = $pdo->prepare("DELETE FROM Services WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Service deleted successfully!";
    }
    
    if(isset($success)) header("Location: manage_services.php?success=" . urlencode($success));
    else header("Location: manage_services.php");
    exit;
}

require_once 'includes/header.php';
$services = $pdo->query("SELECT * FROM Services ORDER BY name")->fetchAll();
?>

<div class="row align-items-center mt-4 mb-4 g-3">
    <div class="col-md-6">
        <h2 class="text-white mb-0"><i class="fa-solid fa-bell-concierge me-2 text-primary"></i> Manage Services</h2>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="admin_dashboard.php" class="btn btn-outline-light">
            <i class="fa-solid fa-arrow-left me-2"></i> Dashboard
        </a>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm animate__animated animate__fadeIn"><?= e($_GET['success']) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add Service Sidebar -->
    <div class="col-lg-4">
        <div class="card shadow-2xl border-0 overflow-hidden h-100" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
            <div class="card-header border-0 py-3" style="background: rgba(255,255,255,0.05);">
                <h5 class="mb-0 fw-bold text-white"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>New Service</h5>
            </div>
            <div class="card-body py-4">
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="add_service" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">SERVICE NAME</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Academic Registry">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">ASSIGNED STAFF/FACULTY</label>
                        <input type="text" name="faculty_name" class="form-control" placeholder="e.g. Prof. Olusegun">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">BUILDING / OFFICE</label>
                        <input type="text" name="building" class="form-control" placeholder="e.g. Level 2, Room 204">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">DESCRIPTION</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe this service..."></textarea>
                    </div>
                    
                    <button class="btn btn-primary w-100 fw-bold py-2">
                        <i class="fa-solid fa-plus me-2"></i> Create Service
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Services List -->
    <div class="col-lg-8">
        <div class="card shadow-2xl border-0 overflow-hidden" style="background: var(--bg-card); border: 1px solid var(--glass-border) !important;">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background: rgba(255,255,255,0.05);">
                        <tr>
                            <th class="px-4 py-3 text-muted small">SERVICE</th>
                            <th class="py-3 text-muted small">STAFF & LOCATION</th>
                            <th class="text-end px-4 py-3 text-muted small">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($services as $s): ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-white fs-5"><?= e($s['name']) ?></div>
                                    <div class="text-muted small"><?= e($s['description'] ?: 'No description provided.') ?></div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fa-solid fa-user-tie text-secondary me-2 small"></i>
                                        <span class="text-white small fw-bold"><?= e($s['faculty_name'] ?? 'Staff') ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-location-dot text-primary me-2 small"></i>
                                        <span class="text-muted small"><?= e($s['building'] ?? 'Main Complex') ?></span>
                                    </div>
                                </td>
                                <td class="text-end px-4 py-3">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-primary border-0 hover-lift" 
                                                data-bs-toggle="modal" data-bs-target="#editService<?= $s['id'] ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="" method="POST" onsubmit="return confirm('Deleting this service will remove all associated queue data. Continue?');">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="delete_service" value="1">
                                            <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger border-0 hover-lift">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editService<?= $s['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content text-start" style="background: var(--bg-surface); border: 1px solid var(--glass-border);">
                                                <form action="" method="POST">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title fw-bold text-white">Edit Service</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body py-4">
                                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                        <input type="hidden" name="update_service" value="1">
                                                        <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small fw-bold">SERVICE NAME</label>
                                                            <input type="text" name="name" class="form-control" value="<?= e($s['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small fw-bold">FACULTY / STAFF</label>
                                                            <input type="text" name="faculty_name" class="form-control" value="<?= e($s['faculty_name']) ?>">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small fw-bold">LOCATION</label>
                                                            <input type="text" name="building" class="form-control" value="<?= e($s['building']) ?>">
                                                        </div>
                                                        <div class="mb-0">
                                                            <label class="form-label text-muted small fw-bold">DESCRIPTION</label>
                                                            <textarea name="description" class="form-control" rows="3"><?= e($s['description']) ?></textarea>
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
                        <?php if(empty($services)): ?>
                            <tr><td colspan="3" class="text-center py-5 text-muted"><i class="fa-solid fa-inbox fa-3x d-block mb-3 opacity-25"></i>No services defined.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
