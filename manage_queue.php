<?php
require_once 'includes/auth_check.php';
require_role('staff');
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'api/email/send_email.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    
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

        // ── PROXIMITY NOTIFICATION ───────────────────────────────────────────
        // Notify the person who is now 3rd in line for this staff/service
        $proximity_stmt = $pdo->prepare("
            SELECT q.id, a.user_id, u.name, u.email
            FROM Queue q
            JOIN Appointments a ON q.appointment_id = a.id
            JOIN Services s ON a.service_id = s.id
            JOIN Users u ON a.user_id = u.id
            WHERE a.date = ? AND q.status = 'waiting' AND s.faculty_name = ?
            ORDER BY q.position ASC
            LIMIT 1 OFFSET 2
        ");
        $proximity_stmt->execute([$today, $faculty_name]);
        $target = $proximity_stmt->fetch(PDO::FETCH_ASSOC);

        if ($target) {
            $prox_msg = "You are currently 3rd in line for " . $service_name . ".\n\nPlease start heading towards " . $building . " if you haven't already.";
            $notify_prox = $pdo->prepare("INSERT INTO Notifications (user_id, message, type, status) VALUES (?, ?, 'in-app', 'unread')");
            $notify_prox->execute([$target['user_id'], $prox_msg]);
            
            $email_prox = "Hello " . $target['name'] . ",\n\n" . $prox_msg . "\n\nSee you soon!";
            send_notification_email($target['email'], $target['name'], "Incoming Appointment - " . $service_name, $email_prox);
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
    elseif($action === 'missed') {
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
        $stmt = $pdo->prepare("UPDATE Queue SET status = 'missed' WHERE id = ?");
        $stmt->execute([$queue_id]);
        
        // Update appointment
        $upd_appt = $pdo->prepare("UPDATE Appointments SET status = 'missed' WHERE id = ?");
        $upd_appt->execute([$info['appointment_id']]);
        
        // Create missed message
        $msg = "We called your number for " . $info['service_name'] . " but you were not present.\n\nYour appointment has been marked as missed. You can rejoin the queue from your dashboard if you are still on-site.";
        
        // Save notification
        $notify = $pdo->prepare("INSERT INTO Notifications (user_id, message, type, status) VALUES (?, ?, 'in-app', 'unread')");
        $notify->execute([$student_id, $msg]);
        
        header("Location: staff_dashboard.php?warning=Marked as missed");
        exit();
    }
}

header("Location: staff_dashboard.php");
exit();
?>