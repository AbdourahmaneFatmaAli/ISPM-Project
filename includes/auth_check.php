<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: /DQSSA/login.php");
    exit;
}
function require_role($role) {
    if ($_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
        die("Unauthorized access.");
    }
}
?>
