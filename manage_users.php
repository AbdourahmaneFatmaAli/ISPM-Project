<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    if(isset($_POST['update_role'])) {
        $id = $_POST['user_id'];
        $role = $_POST['role'];
        if($id != $_SESSION['user_id']) { 
            $stmt = $pdo->prepare("UPDATE Users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $id]);
        }
    } elseif(isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];
        if($id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    header("Location: manage_users.php");
    exit;
}

require_once 'includes/header.php';
$stmt = $pdo->query("SELECT id, name, email, role, created_at FROM Users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<div class="mt-4 mb-4 d-flex justify-content-between align-items-center">
    <h2><i class="fa-solid fa-users me-2"></i> Manage Users</h2>
    <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="card shadow-sm border-0 mb-5">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="py-3">Name</th>
                    <th class="py-3">Email</th>
                    <th class="py-3">Role</th>
                    <th class="py-3">Registered</th>
                    <th class="text-end px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                    <tr>
                        <td class="px-4 align-middle text-muted">#<?= e($u['id']) ?></td>
                        <td class="align-middle fw-bold"><?= e($u['name']) ?></td>
                        <td class="align-middle"><?= e($u['email']) ?></td>
                        <td class="align-middle">
                            <span class="badge <?= $u['role'] == 'admin' ? 'bg-danger' : ($u['role']=='staff' ? 'bg-success' : 'bg-primary') ?>">
                                <?= e(strtoupper($u['role'])) ?>
                            </span>
                        </td>
                        <td class="align-middle text-muted small"><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
                        <td class="text-end px-4 align-middle">
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#roleModal<?= $u['id'] ?>"><i class="fa-solid fa-user-pen"></i></button>
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete this user completely?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="delete_user" value="1">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                                
                                <div class="modal fade text-start" id="roleModal<?= $u['id'] ?>" tabindex="-1">
                                  <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                      <form action="" method="POST">
                                          <div class="modal-header border-bottom-0">
                                            <h5 class="modal-title fw-bold">Change Role: <?= e($u['name']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                          </div>
                                          <div class="modal-body py-4">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="update_role" value="1">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <label class="form-label fw-bold">Select New Role</label>
                                            <select name="role" class="form-select form-select-lg">
                                                <option value="student" <?= $u['role']=='student'?'selected':'' ?>>Student</option>
                                                <option value="staff" <?= $u['role']=='staff'?'selected':'' ?>>Staff</option>
                                                <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                            </select>
                                          </div>
                                          <div class="modal-footer border-top-0 bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold px-4">Save changes</button>
                                          </div>
                                      </form>
                                    </div>
                                  </div>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-light text-dark border p-2">You (Admin)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
