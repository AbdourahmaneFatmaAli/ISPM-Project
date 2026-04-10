<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../email/send_email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    
    $user_id = $_SESSION['user_id'];
    $service_id = $_POST['service_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    // --- SLOT AVAILABILITY CHECK (30-minute window) ---
    // Check if any active appointment for this service/date overlaps with the 30-min window
    $check_stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM Appointments 
        WHERE service_id = ? 
          AND date = ? 
          AND status NOT IN ('cancelled', 'missed') 
          AND ABS(TIMESTAMPDIFF(MINUTE, time, ?)) < 30
    ");
    $check_stmt->execute([$service_id, $date, $time]);
    $overlap_count = $check_stmt->fetchColumn();

    if ($overlap_count > 0) {
        header("Location: ../../book.php?error=This+time+slot+is+already+reserved.+Please+choose+a+time+at+least+30+minutes+apart.");
        exit;
    }
    // --------------------------------------------------
    
    // Get service details with faculty name and building
    $svc_stmt = $pdo->prepare("SELECT name, faculty_name, building FROM Services WHERE id = ?");
    $svc_stmt->execute([$service_id]);
    $service = $svc_stmt->fetch(PDO::FETCH_ASSOC);
    
    $service_name = $service['name'];
    $faculty_name = $service['faculty_name'] ?: 'Staff Member';
    $building = $service['building'] ?: 'Main Building';
    
    // Generate unique QR payload
    $qr_code = "APPT-" . uniqid() . "-USR-" . $user_id;

    $stmt = $pdo->prepare("INSERT INTO Appointments (user_id, service_id, date, time, qr_code) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $service_id, $date, $time, $qr_code])) {
        $appointment_id = $pdo->lastInsertId();
        
        // Add to queue
        $pos_query = $pdo->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM Queue");
        $pos_query->execute();
        $position = $pos_query->fetchColumn();
        
        $queue_stmt = $pdo->prepare("INSERT INTO Queue (appointment_id, position, status) VALUES (?, ?, 'waiting')");
        $queue_stmt->execute([$appointment_id, $position]);
        
        // 1. In-App Notification with faculty name and location
        $msg = "Your appointment for {$service_name} on {$date} at {$time} is confirmed.\n\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "Staff: {$faculty_name}\n";
        $msg .= "Location: {$building}\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "Queue Position: #{$position}\n";
        $msg .= "Please arrive 10 minutes before your scheduled time.\n\n";
        $msg .= "You will be notified when it's your turn.";
        
        $notify = $pdo->prepare("INSERT INTO Notifications (user_id, message, type, status) VALUES (?, ?, 'in-app', 'unread')");
        $notify->execute([$user_id, $msg]);
        
        // 2. Email Notification with faculty name and location
        $user_email_stmt = $pdo->prepare("SELECT email, name FROM Users WHERE id = ?");
        $user_email_stmt->execute([$user_id]);
        $u = $user_email_stmt->fetch();
        
        $email_body = "Hello {$u['name']},\n\n";
        $email_body .= "Your appointment has been successfully booked!\n\n";
        $email_body .= "Service: {$service_name}\n";
        $email_body .= "Staff: {$faculty_name}\n";
        $email_body .= "Location: {$building}\n";
        $email_body .= "Date: {$date}\n";
        $email_body .= "Time: {$time}\n";
        $email_body .= "Queue Position: #{$position}\n";
      
        $email_body .= "Use your QR code to check in at the counter.\n\n";
        $email_body .= "You will receive a notification when it's your turn.\n\n";
        $email_body .= "Thank you for using DQSSA!";
        
        send_notification_email($u['email'], $u['name'], 'Appointment Confirmation - DQSSA', $email_body);

        header("Location: ../../my_appointments.php?success=Appointment+successfully+booked!");
    } else {
        header("Location: ../../book.php?error=Booking+failed");
    }
}
?>