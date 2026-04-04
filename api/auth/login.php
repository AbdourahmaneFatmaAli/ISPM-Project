<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, name, password, role FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../../admin_dashboard.php");
        } elseif ($user['role'] === 'staff') {
            header("Location: ../../staff_dashboard.php");
        } else {
            header("Location: ../../dashboard.php");
        }
    } else {
        header("Location: ../../login.php?error=Invalid+email+or+password");
    }
}
