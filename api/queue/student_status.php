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

try {
    // Find the student's active queue entry for today
    $stmt = $pdo->prepare("
        SELECT q.position, q.status as queue_status, s.name as service_name, s.id as service_id
        FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        JOIN Services s ON a.service_id = s.id
        WHERE a.user_id = ? AND a.date = ? AND q.status != 'done'
        LIMIT 1
    ");
    $stmt->execute([$user_id, $today]);
    $my_queue = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$my_queue) {
        echo json_encode(['status' => 'success', 'data' => null]);
        exit;
    }

    // Calculate how many people are before them in the same service
    $before_stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        WHERE a.service_id = ? AND a.date = ? AND q.status = 'waiting' AND q.position < ?
    ");
    $before_stmt->execute([$my_queue['service_id'], $today, $my_queue['position']]);
    $people_ahead = $before_stmt->fetchColumn();

    // Check if anyone is currently being served for this service
    $serving_stmt = $pdo->prepare("
        SELECT position 
        FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        WHERE a.service_id = ? AND a.date = ? AND q.status = 'serving'
        LIMIT 1
    ");
    $serving_stmt->execute([$my_queue['service_id'], $today]);
    $current_serving = $serving_stmt->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'service_name' => $my_queue['service_name'],
            'my_position' => (int)$my_queue['position'],
            'queue_status' => $my_queue['queue_status'],
            'people_ahead' => (int)$people_ahead,
            'current_serving' => $current_serving ? (int)$current_serving : null
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
