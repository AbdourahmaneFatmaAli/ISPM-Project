<?php
require_once 'includes/auth_check.php';
require_role('admin');
require_once 'config/database.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!validate_csrf_token($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    if(isset($_POST['add_user'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $role]);
            $success = "User added successfully!";
        } catch(PDOException $e) {
            $error = "Error adding user: " . $e->getMessage();
        }
    } elseif(isset($_POST['update_user'])) {
        $id = $_POST['user_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        
        if($id != $_SESSION['user_id']) { 
            try {
                $stmt = $pdo->prepare("UPDATE Users SET name = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$name, $email, $role, $id]);
                $success = "User updated successfully!";
            } catch(PDOException $e) {
                $error = "Update failed: " . $e->getMessage();
            }
        }
    } elseif(isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];
        if($id != $_SESSION['user_id']) {
            $pdo->prepare("DELETE FROM Users WHERE id = ?")->execute([$id]);
            $success = "User deleted successfully!";
        }
    }
    
    if(isset($success)) header("Location: manage_users.php?success=" . urlencode($success));
    elseif(isset($error)) header("Location: manage_users.php?error=" . urlencode($error));
    else header("Location: manage_users.php");
    exit;
}

require_once 'includes/header.php';
$stmt = $pdo->query("SELECT id, name, email, role, created_at FROM Users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="row align-items-center mt-4 mb-4 g-3">
    <div class="col-md-6">
        <h2 class="text-white mb-0"><i class="fa-solid fa-users me-2 text-primary"></i> Manage Users</h2>
    </div>
    <div class="col-md-6 text-md-end">
        <button class="btn btn-primary px-4 fw-bold me-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fa-solid fa-user-plus me-2"></i> Add New User
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
                    <th class="px-4 py-3 text-muted small">USER</th>
                    <th class="py-3 text-muted small">ROLE</th>
                    <th class="py-3 text-muted small">REGISTERED</th>
                    <th class="text-end px-4 py-3 text-muted small">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                    <i class="fa-solid fa-user text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white"><?= e($u['name']) ?></div>
                                    <div class="small text-muted"><?= e($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge rounded-pill <?= $u['role'] == 'admin' ? 'bg-danger text-white' : ($u['role']=='staff' ? 'bg-success text-white' : 'bg-primary text-white') ?>" style="font-weight: 600; padding: 0.5em 1.2em;">
                                <?= strtoupper(e($u['role'])) ?>
                            </span>
                        </td>
                        <td class="py-3 text-muted small">
                            <?= date('M j, Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td class="text-end px-4 py-3">
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-outline-primary me-1 border-0 hover-lift" 
                                        data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>" title="Edit User">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="delete_user" value="1">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger border-0 hover-lift" title="Delete User">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>

                                <!-- Edit User Modal -->
                                <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--glass-border);">
                                            <form action="" method="POST">
                                                <div class="modal-header border-0">
                                                    <h5 class="modal-title fw-bold text-white">Edit User: <?= e($u['name']) ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start py-4">
                                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                    <input type="hidden" name="update_user" value="1">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small fw-bold">FULL NAME</label>
                                                        <input type="text" name="name" class="form-control" value="<?= e($u['name']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small fw-bold">EMAIL ADDRESS</label>
                                                        <input type="email" name="email" class="form-control" value="<?= e($u['email']) ?>" required>
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="form-label text-muted small fw-bold">SYSTEM ROLE</label>
                                                        <select name="role" class="form-select">
                                                            <option value="student" <?= $u['role']=='student'?'selected':'' ?>>Student</option>
                                                            <option value="staff" <?= $u['role']=='staff'?'selected':'' ?>>Staff</option>
                                                            <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-outline-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Update Account</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-secondary opacity-50 px-3 py-2">Master Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--glass-border);">
            <form action="" method="POST">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-white">Add New System User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="add_user" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">FULL NAME</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter user's name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control" placeholder="example@dqssa.edu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">INITIAL PASSWORD</label>
                        <input type="password" name="password" class="form-control" placeholder="Create temporary password" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small fw-bold">SYSTEM ROLE</label>
                        <select name="role" class="form-select">
                            <option value="student">Student</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">Create User Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
