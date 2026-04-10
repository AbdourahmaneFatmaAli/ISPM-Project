<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../config/config.php';

// This API returns the current serving number and total waiting for each service today
$today = date('Y-m-d');

try {
    // 1. Get current serving position per service
    $serving_stmt = $pdo->prepare("
        SELECT s.id as service_id, s.name as service_name, q.position as current_serving
        FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        JOIN Services s ON a.service_id = s.id
        WHERE a.date = ? AND q.status = 'serving'
    ");
    $serving_stmt->execute([$today]);
    $serving_data = $serving_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get total waiting per service
    $waiting_stmt = $pdo->prepare("
        SELECT s.id as service_id, COUNT(q.id) as waiting_count
        FROM Queue q
        JOIN Appointments a ON q.appointment_id = a.id
        JOIN Services s ON a.service_id = s.id
        WHERE a.date = ? AND q.status = 'waiting'
        GROUP BY s.id
    ");
    $waiting_stmt->execute([$today]);
    $waiting_data = $waiting_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine data
    $services = [];
    
    // Initialize with all services to ensure we return 0 if no one is waiting/serving
    $all_services = $pdo->query("SELECT id, name FROM Services")->fetchAll(PDO::FETCH_ASSOC);
    foreach($all_services as $s) {
        $services[$s['id']] = [
            'service_name' => $s['name'],
            'current_serving' => null,
            'waiting_count' => 0
        ];
    }

    foreach($serving_data as $row) {
        $services[$row['service_id']]['current_serving'] = (int)$row['current_serving'];
    }

    foreach($waiting_data as $row) {
        $services[$row['service_id']]['waiting_count'] = (int)$row['waiting_count'];
    }

    echo json_encode([
        'status' => 'success',
        'data' => array_values($services)
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
