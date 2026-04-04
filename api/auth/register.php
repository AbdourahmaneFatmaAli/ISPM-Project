<?php
require_once '../../config/database.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'student'; // Default registration is for students
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->rowCount() > 0){
        header("Location: ../../register.php?error=Email+already+exists");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $password, $role])) {
        header("Location: ../../login.php?success=Registration+successful.+Please+log+in.");
    } else {
        header("Location: ../../register.php?error=Registration+failed");
    }
}
