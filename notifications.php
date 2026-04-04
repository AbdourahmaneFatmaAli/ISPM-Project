<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php';

// Mark all as read
$upd = $pdo->prepare("UPDATE Notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'");
$upd->execute([$_SESSION['user_id']]);

$stmt = $pdo->prepare("SELECT * FROM Notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>
<div class="mt-4 mb-4">
    <h2><i class="fa-regular fa-bell me-2"></i> All Notifications</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="list-group list-group-flush">
        <?php if(empty($notifications)): ?>
            <div class="text-center py-5 text-muted">You have no notifications.</div>
        <?php else: ?>
            <?php foreach($notifications as $notif): ?>
                <div class="list-group-item p-4">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 fw-bold">
                            <i class="fa-solid fa-circle-info me-2 text-info"></i> 
                            System Message
                        </h5>
                        <small class="text-muted">
                            <i class="fa-regular fa-clock me-1"></i>
                            <?= date('M j, Y g:i A', strtotime($notif['created_at'])) ?>
                        </small>
                    </div>
                    <div class="mb-1 mt-3 p-3" style="background: #f8f9fa; border-radius: 10px; border-left: 4px solid #0d6efd;">
                        <?php 
                        // Convert newlines to <br> tags and display the message
                        $display_message = nl2br(htmlspecialchars($notif['message']));
                        ?>
                        <p class="mb-0 fs-6" style="line-height: 1.6;"><?= $display_message ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .list-group-item {
        transition: all 0.3s ease;
    }
    .list-group-item:hover {
        background-color: #fefefe;
        transform: translateX(5px);
    }
</style>

<?php require_once 'includes/footer.php'; ?>