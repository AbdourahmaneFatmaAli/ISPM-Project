<?php
require_once 'includes/auth_check.php';
require_role('staff');
require_once 'config/database.php';
require_once 'api/email/send_email.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $queue_id = $_POST['queue_id'];
    $action = $_POST['action'];
    $student_id = $_POST['student_id'];
    
    if($action === 'call') {
        // Update queue status
        $stmt = $pdo->prepare("UPDATE Queue SET status = 'serving' WHERE id = ?");
        $stmt->execute([$queue_id]);
        
        // Get service details with faculty name and building
        $service_query = $pdo->prepare("
            SELECT s.name, s.faculty_name, s.building
            FROM Queue q
            JOIN Appointments a ON q.appointment_id = a.id
            JOIN Services s ON a.service_id = s.id
            WHERE q.id = ?
        ");
        $service_query->execute([$queue_id]);
        $service = $service_query->fetch(PDO::FETCH_ASSOC);
        
        $service_name = $service['name'];
        $faculty_name = $service['faculty_name'];
        $building = $service['building'];
        
        // Create the notification message
        $msg = "You are next for " . $service_name . "!\n\n";
        $msg .= "Staff: " . $faculty_name . "\n";
        $msg .= "Location: " . $building . "\n\n";
        $msg .= "Please proceed to the service counter.";
        
        // Save to database
        $notify = $pdo->prepare("INSERT INTO Notifications (user_id, message, type, status) VALUES (?, ?, 'in-app', 'unread')");
        $notify->execute([$student_id, $msg]);
        
        // Send email
        $u_stmt = $pdo->prepare("SELECT email, name FROM Users WHERE id = ?");
        $u_stmt->execute([$student_id]);
        $user = $u_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user) {
            $email_body = "Hello " . $user['name'] . ",\n\n";
            $email_body .= "It's your turn for " . $service_name . "!\n\n";
            $email_body .= "━━━━━━━━━━━━━━━━━━━━━━\n";
            $email_body .= "Staff: " . $faculty_name . "\n";
            $email_body .= "Location: " . $building . "\n";
            $email_body .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $email_body .= "Please proceed to the service counter.\n\n";
            $email_body .= "Thank you!";
            
            send_notification_email($user['email'], $user['name'], "Your Turn - " . $service_name, $email_body);
        }
        
        header("Location: staff_dashboard.php?success=Called successfully");
        exit();
    }
    elseif($action === 'complete') {
        // Get appointment info
        $qInfo = $pdo->prepare("
            SELECT q.appointment_id, s.name as service_name
            FROM Queue q
            JOIN Appointments a ON q.appointment_id = a.id
            JOIN Services s ON a.service_id = s.id
            WHERE q.id = ?
        ");
        $qInfo->execute([$queue_id]);
        $info = $qInfo->fetch(PDO::FETCH_ASSOC);
        
        // Update queue
        $stmt = $pdo->prepare("UPDATE Queue SET status = 'done' WHERE id = ?");
        $stmt->execute([$queue_id]);
        
        // Update appointment
        $upd_appt = $pdo->prepare("UPDATE Appointments SET status = 'completed' WHERE id = ?");
        $upd_appt->execute([$info['appointment_id']]);
        
        // Create completion message
        $msg = "Your " . $info['service_name'] . " service has been completed.\n\nThank you for visiting DQSSA!";
        
        // Save notification
        $notify = $pdo->prepare("INSERT INTO Notifications (user_id, message, type, status) VALUES (?, ?, 'in-app', 'unread')");
        $notify->execute([$student_id, $msg]);
        
        header("Location: staff_dashboard.php?success=Completed successfully");
        exit();
    }
}

header("Location: staff_dashboard.php");
exit();
?>