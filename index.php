<?php require_once 'includes/header.php'; ?>

<div class="hero-section text-center">
    <h1 class="display-3 fw-bold mb-4">Welcome to DQSSA</h1>
    <p class="lead fs-4 mb-5 opacity-75">Online Appointment Booking & Queue Management System</p>
    <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="btn btn-light btn-lg px-5 shadow-sm me-3 text-primary"><i class="fa-solid fa-user-plus me-2"></i>Get Started</a>
        <a href="login.php" class="btn btn-outline-light btn-lg px-5"><i class="fa-solid fa-right-to-bracket me-2"></i>Login</a>
    <?php else: ?>
        <a href="dashboard.php" class="btn btn-light btn-lg px-5 shadow-sm text-primary"><i class="fa-solid fa-house me-2"></i>Go to Dashboard</a>
    <?php endif; ?>
</div>

<div class="row align-items-stretch mt-5 mb-5 g-4">
    <div class="col-md-4 text-center">
        <div class="card h-100 p-4 hover-lift">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center bg-primary" style="width:70px; height:70px; border-radius:50%; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4);">
                <i class="fa-solid fa-calendar-check fa-2x text-white"></i>
            </div>
            <h3 class="h4 fw-bold mb-3">Easy Booking</h3>
            <p class="text-muted mb-0">Book appointments online effortlessly without the hassle. Choose your service and time.</p>
        </div>
    </div>
    <div class="col-md-4 text-center">
        <div class="card h-100 p-4 hover-lift">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center bg-success" style="width:70px; height:70px; border-radius:50%; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                <i class="fa-solid fa-qrcode fa-2x text-white"></i>
            </div>
            <h3 class="h4 fw-bold mb-3">QR Check-in</h3>
            <p class="text-muted mb-0">Instantly check-in using a dynamic QR code from your phone when you arrive.</p>
        </div>
    </div>
    <div class="col-md-4 text-center">
        <div class="card h-100 p-4 hover-lift">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center bg-warning" style="width:70px; height:70px; border-radius:50%; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);">
                <i class="fa-solid fa-bell fa-2x text-white"></i>
            </div>
            <h3 class="h4 fw-bold mb-3">Stay Notified</h3>
            <p class="text-muted mb-0">Receive real-time email and in-app notifications on your queue status.</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
