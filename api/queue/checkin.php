<?php
header('Content-Type: application/json');
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';
require_once '../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$qr_code = $_POST['qr_code'] ?? '';

if (empty($qr_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid QR Code']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Find the appointment matching the QR code
    $stmt = $pdo->prepare("
        SELECT id, service_id, status, date 
        FROM Appointments 
        WHERE qr_code = ? AND user_id = ?
    ");
    $stmt->execute([$qr_code, $user_id]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appt) {
        throw new Exception("Invalid QR code. No appointment found.");
    }

    if ($appt['date'] !== $today) {
        $appt_date = date('M d, Y', strtotime($appt['date']));
        throw new Exception("This appointment is scheduled for $appt_date. You can only join the queue on the day of your appointment.");
    }

    if (!in_array($appt['status'], ['booked', 'missed'])) {
        throw new Exception("Already in queue or appointment completed.");
    }

    // 2. Update appointment status
    $upd = $pdo->prepare("UPDATE Appointments SET status = 'checked-in' WHERE id = ?");
    $upd->execute([$appt['id']]);

    // 3. Calculate next position for this service/day
    $pos_stmt = $pdo->prepare("
        SELECT COALESCE(MAX(q.position), 0) + 1
        FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        WHERE a.date = ?
    ");
    $pos_stmt->execute([$today]);
    $next_position = $pos_stmt->fetchColumn();

    // 4. Insert into Queue
    $q_stmt = $pdo->prepare("INSERT INTO Queue (appointment_id, position, status) VALUES (?, ?, 'waiting')");
    $q_stmt->execute([$appt['id'], $next_position]);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Successfully joined the queue!', 'position' => $next_position]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
