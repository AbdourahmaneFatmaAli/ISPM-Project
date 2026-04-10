<?php require_once 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="row align-items-center mb-5 py-5 animate__animated animate__fadeIn">
    <div class="col-lg-6 mb-5 mb-lg-0">
        <h6 class="text-primary fw-bold text-uppercase mb-3 letter-spacing-lg animate__animated animate__fadeInDown" style="letter-spacing: 2px;">
            <i class="fa-solid fa-graduation-cap me-2"></i> Pan-Atlantic University
        </h6>
        <h1 class="display-3 fw-bolder mb-4 animate__animated animate__fadeInLeft">
            Excellence in Service, <br>
            <span class="text-primary">Zero Wait Time.</span>
        </h1>
        <p class="lead text-muted mb-5 pe-lg-5 animate__animated animate__fadeInLeft animate__delay-1s" style="font-size: 1.25rem;">
            Experience the next level of student service at Pan-Atlantic University. Book, track, and manage your school services with our premium digital queue system.
        </p>
        <div class="d-flex flex-wrap gap-3 animate__animated animate__fadeInUp animate__delay-1s">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-primary btn-lg shadow-lg px-5 py-3">
                    Get Started <i class="fa-solid fa-chevron-right ms-2 small"></i>
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg px-5 py-3">
                    Log In
                </a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg shadow-lg px-5 py-3">
                    Go to Dashboard <i class="fa-solid fa-house ms-2"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="position-relative animate__animated animate__zoomIn animate__delay-1s">
            <!-- Decorative Glows -->
            <div class="position-absolute top-50 start-50 translate-middle bg-secondary opacity-20" style="width: 500px; height: 500px; filter: blur(120px); border-radius: 50%;"></div>
            
            <div class="card overflow-hidden shadow-2xl border-0" style="background: var(--bg-card); border: 1px solid var(--glass-border);">
                <div class="p-2">
                    <img src="<?= BASE_URL ?>assets/images/pau_hero.png" 
                         class="img-fluid rounded-3" alt="PAU Modern Architecture">
                </div>
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-shield-halved text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-white">Official PAU Service</h6>
                            <span class="small text-muted">Integrated Queue Terminal</span>
                        </div>
                    </div>
                    <div class="px-3 py-1 rounded-pill bg-success bg-opacity-10 text-success fw-bold small">
                        <i class="fa-solid fa-circle me-1 small animate__animated animate__pulse animate__infinite"></i> Online
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div id="features" class="row g-4 mt-5 pb-5">
    <div class="col-md-4">
        <div class="card p-4 h-100 animate__animated animate__fadeInUp border-0" style="background: var(--glass-bg); border: 1px solid var(--glass-border) !important;">
            <div class="bg-primary bg-opacity-10 rounded-4 p-3 d-inline-block mb-4 text-center" style="width: 60px;">
                <i class="fa-solid fa-qrcode fa-2x text-primary"></i>
            </div>
            <h4 class="fw-bold mb-3 text-white">Digital Check-in</h4>
            <p class="text-muted small mb-0">Instant QR-based or remote check-in via your dashboard. No physical lines required.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100 animate__animated animate__fadeInUp animate__delay-1s border-0" style="background: var(--glass-bg); border: 1px solid var(--glass-border) !important;">
            <div class="bg-secondary bg-opacity-10 rounded-4 p-3 d-inline-block mb-4 text-center" style="width: 60px;">
                <i class="fa-solid fa-clock fa-2x text-secondary"></i>
            </div>
            <h4 class="fw-bold mb-3 text-white">Live Tracking</h4>
            <p class="text-muted small mb-0">Watch your position update in real-time. Know exactly when it's your turn from anywhere on campus.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100 animate__animated animate__fadeInUp animate__delay-2s border-0" style="background: var(--glass-bg); border: 1px solid var(--glass-border) !important;">
            <div class="bg-accent bg-opacity-10 rounded-4 p-3 d-inline-block mb-4 text-center" style="width: 60px;">
                <i class="fa-solid fa-bell-concierge fa-2x text-accent"></i>
            </div>
            <h4 class="fw-bold mb-3 text-white">Smart Alerts</h4>
            <p class="text-muted small mb-0">Receive automated proximity notifications via email and in-app alerts as you move to the top of the line.</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
