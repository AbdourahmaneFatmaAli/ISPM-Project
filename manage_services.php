<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['add_service'])) {
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        $stmt = $pdo->prepare("INSERT INTO Services (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
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
                    <input type="hidden" name="add_service" value="1">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Service Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. General Checkup">
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold mb-1">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the service..."></textarea>
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
                        <tr><th class="px-4 py-3">Name</th><th class="py-3">Description</th><th class="text-end px-4 py-3">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($services as $s): ?>
                            <tr>
                                <td class="px-4 align-middle fw-bold"><?= htmlspecialchars($s['name']) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($s['description']) ?></td>
                                <td class="text-end px-4 align-middle">
                                    <form action="" method="POST" onsubmit="return confirm('Deleting this service will remove all associated appointments. Are you sure?');">
                                        <input type="hidden" name="delete_service" value="1">
                                        <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
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
