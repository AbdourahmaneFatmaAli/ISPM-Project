<?php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/database.php';
    }
    $n_stmt = $pdo->prepare("SELECT count(*) FROM Notifications WHERE user_id = ? AND status = 'unread'");
    $n_stmt->execute([$_SESSION['user_id']]);
    $unread_count = $n_stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAU Digital Queue | ISPM Modernization</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css for entrance animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Custom Design System -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="animate__animated animate__fadeIn">
<nav class="navbar navbar-expand-lg navbar-glass sticky-top mb-5">
  <div class="container px-4">
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>index.php">
      <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
        <i class="fa-solid fa-building-columns text-primary fs-4"></i>
      </div>
      <span class="fw-bolder">PAU QUEUE</span>
    </a>
    
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navItems">
      <div class="bg-surface p-2 rounded-2 border border-white border-opacity-10">
        <i class="fa-solid fa-bars-staggered text-primary"></i>
      </div>
    </button>
    
    <div class="collapse navbar-collapse" id="navItems">
      <ul class="navbar-nav ms-auto align-items-center fw-medium mt-3 mt-lg-0 py-2">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] === 'student'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>book.php">Book Now</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>my_appointments.php">Appointments</a></li>
            <?php elseif($_SESSION['role'] === 'staff'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>staff_dashboard.php">Queue Control</a></li>
            <?php elseif($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin_dashboard.php">Admin Panel</a></li>
            <?php endif; ?>
            
            <li class="nav-item ms-lg-4 mt-3 mt-lg-0 d-flex align-items-center">
                <a class="nav-link position-relative me-4" href="<?= BASE_URL ?>notifications.php" aria-label="Notifications">
                    <i class="fa-solid fa-bell-concierge fs-5"></i>
                    <?php if($unread_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger p-1" style="width: 18px; height: 18px; font-size: 0.6em; border: 2px solid var(--bg-deep);">
                            <?= $unread_count ?>
                        </span>
                    <?php endif; ?>
                </a>
                
                <div class="d-flex align-items-center bg-white bg-opacity-5 border border-white border-opacity-10 px-3 py-2 rounded-pill shadow-sm">
                    <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.7rem;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <span class="small fw-bold text-white me-3"><?= htmlspecialchars($_SESSION['name']) ?></span>
                    <a class="text-danger small fw-bold text-decoration-none" href="<?= BASE_URL ?>api/auth/logout.php" title="Logout">
                        <i class="fa-solid fa-power-off"></i>
                    </a>
                </div>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link mx-lg-2" href="<?= BASE_URL ?>login.php">Log In</a></li>
            <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                <a class="btn btn-primary rounded-pill px-4 shadow-lg" href="<?= BASE_URL ?>register.php">
                    Get Started <i class="fa-solid fa-chevron-right ms-2 small"></i>
                </a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container pb-5">
