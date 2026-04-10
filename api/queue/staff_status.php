<?php
header('Content-Type: application/json');
require_once '../../includes/auth_check.php';
require_role('staff');
require_once '../../config/database.php';
require_once '../../config/config.php';

$staff_name = $_SESSION['name'] ?? '';
$today = date('Y-m-d');

try {
    // 1. Get current student being served for THIS staff member's services
    $servingStmt = $pdo->prepare("
        SELECT q.*, a.time, a.id as appointment_id, s.name as service_name, u.name as student_name, u.id as student_id
        FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        JOIN Services s ON a.service_id = s.id
        JOIN Users u ON a.user_id = u.id
        WHERE a.date = ? AND q.status = 'serving' AND s.faculty_name = ?
        ORDER BY q.position ASC LIMIT 1
    ");
    $servingStmt->execute([$today, $staff_name]);
    $current = $servingStmt->fetch(PDO::FETCH_ASSOC);

    // 2. Get waiting queue for THIS staff member
    $waitingStmt = $pdo->prepare("
        SELECT q.*, a.time, s.name as service_name, u.name as student_name, u.email as student_email
        FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        JOIN Services s ON a.service_id = s.id
        JOIN Users u ON a.user_id = u.id
        WHERE a.date = ? AND q.status = 'waiting' AND s.faculty_name = ?
        ORDER BY q.position ASC
    ");
    $waitingStmt->execute([$today, $staff_name]);
    $waiting = $waitingStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Stats
    $doneStmt = $pdo->prepare("
        SELECT COUNT(*) FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        JOIN Services s ON a.service_id = s.id
        WHERE a.date = ? AND q.status = 'done' AND s.faculty_name = ?
    ");
    $doneStmt->execute([$today, $staff_name]);
    $doneCount = $doneStmt->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'current' => $current,
            'waiting' => $waiting,
            'stats' => [
                'waiting_count' => count($waiting),
                'serving_count' => $current ? 1 : 0,
                'done_count' => (int)$doneCount
            ]
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
