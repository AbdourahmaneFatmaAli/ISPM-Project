<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    $qr_code = trim($_POST['qr_code']);
    
    // Find appointment
    $stmt = $pdo->prepare("SELECT * FROM Appointments WHERE qr_code = ? AND status = 'booked'");
    $stmt->execute([$qr_code]);
    $appt = $stmt->fetch();
    
    if($appt) {
        $upd = $pdo->prepare("UPDATE Appointments SET status = 'checked-in' WHERE id = ?");
        $upd->execute([$appt['id']]);
        
        $posStmt = $pdo->prepare("SELECT MAX(position) as max_pos FROM Queue q JOIN Appointments a ON q.appointment_id = a.id WHERE a.date = ?");
        $posStmt->execute([$appt['date']]);
        $row = $posStmt->fetch();
        $position = ($row['max_pos'] ? $row['max_pos'] : 0) + 1;
        
        $q_stmt = $pdo->prepare("INSERT INTO Queue (appointment_id, position, status) VALUES (?, ?, 'waiting')");
        $q_stmt->execute([$appt['id'], $position]);
        
        $msg = "You have checked in successfully. Your queue number is $position.";
        $notify = $pdo->prepare("INSERT INTO Notifications (user_id, message, type) VALUES (?, ?, 'in-app')");
        $notify->execute([$appt['user_id'], $msg]);
        
        $success = "Check-in successful! Queue number: $position";
    } else {
        $error = "Invalid QR code or appointment already checked in.";
    }
}

require_once 'includes/header.php';
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-sm p-4 text-center">
            <h3><i class="fa-solid fa-qrcode mb-3 fa-2x text-primary"></i> <br> Scan to Check-in</h3>
            <p class="text-muted">For staff/kiosk device</p>
            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success fw-bold"><?= $success ?></div><?php endif; ?>
            
            <form action="" method="POST" class="mt-4">
                <input type="text" name="qr_code" class="form-control mb-3 text-center" placeholder="Click here and scan barcode" autofocus required autocomplete="off">
                <button type="submit" class="btn btn-primary w-100">Process Check-in</button>
            </form>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
