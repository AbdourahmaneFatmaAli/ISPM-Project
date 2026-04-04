<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <title>DQSSA Appointment System</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Design System -->
    <link rel="stylesheet" href="/DQSSA/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-glass sticky-top mb-5">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/DQSSA/index.php">
      <i class="fa-solid fa-hospital-user me-2 text-primary fs-3"></i>
      <span>DQSSA</span>
    </a>
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navItems">
      <span class="fa-solid fa-bars text-primary" style="font-size: 1.5rem;"></span>
    </button>
    <div class="collapse navbar-collapse" id="navItems">
      <ul class="navbar-nav ms-auto align-items-center fw-medium mt-3 mt-lg-0">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] === 'student'): ?>
                <li class="nav-item"><a class="nav-link mx-lg-2" href="/DQSSA/dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link mx-lg-2" href="/DQSSA/book.php">Book Now</a></li>
                <li class="nav-item"><a class="nav-link mx-lg-2" href="/DQSSA/my_appointments.php">My Appointments</a></li>
            <?php elseif($_SESSION['role'] === 'staff'): ?>
                <li class="nav-item"><a class="nav-link mx-lg-2" href="/DQSSA/staff_dashboard.php">Queue Monitor</a></li>
            <?php elseif($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link mx-lg-2" href="/DQSSA/admin_dashboard.php">Admin Panel</a></li>
            <?php endif; ?>
            <li class="nav-item ms-lg-4 mt-3 mt-lg-0 d-flex align-items-center">
                <a class="nav-link position-relative me-3" href="/DQSSA/notifications.php" aria-label="Notifications">
                    <i class="fa-solid fa-bell fs-5 text-dark"></i>
                    <?php if($unread_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65em;">
                            <?= $unread_count ?>
                        </span>
                    <?php endif; ?>
                </a>
                <span class="navbar-text me-3 text-dark fw-bold bg-light px-3 py-1 rounded-pill">
                    <i class="fa-solid fa-circle-user text-primary me-1"></i> <?= htmlspecialchars($_SESSION['name']) ?>
                </span>
                <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="/DQSSA/api/auth/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link mx-lg-2" href="/DQSSA/login.php">Login</a></li>
            <li class="nav-item ms-lg-3 mt-2 mt-lg-0"><a class="btn btn-primary rounded-pill px-4" href="/DQSSA/register.php">Get Started</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container pb-5">
