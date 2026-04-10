<?php require_once 'includes/header.php'; ?>

<div class="row min-vh-100 align-items-center justify-content-center animate__animated animate__fadeIn">
    <div class="col-md-5">
        <div class="card p-4 p-lg-5 Modern-Glass-Card border-0 shadow-2xl">
            <div class="text-center mb-5">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; border: 1px solid rgba(189, 32, 49, 0.3);">
                    <i class="fa-solid fa-lock-open text-primary fs-2"></i>
                </div>
                <h2 class="fw-bolder text-white mb-2">Welcome Back</h2>
                <p class="text-muted">Access your digital queue terminal</p>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert bg-danger bg-opacity-10 text-danger border-0 small mb-4 animate__animated animate__shakeX">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert bg-success bg-opacity-10 text-success border-0 small mb-4">
                    <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>

            <form action="api/auth/login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold text-uppercase">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" name="email" class="form-control border-start-0" 
                               placeholder="Enter your email" required autocomplete="email">
                    </div>
                </div>

                <div class="mb-5">
                    <div class="d-flex justify-content-between">
                        <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                        <a href="#" class="small text-primary text-decoration-none">Forgot?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-start-0" 
                               placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 shadow-lg mb-4">
                    SIGN IN TO DASHBOARD <i class="fa-solid fa-arrow-right ms-2 small"></i>
                </button>

                <div class="text-center">
                    <p class="text-muted small mb-0">No account yet? 
                        <a href="register.php" class="text-primary fw-bold text-decoration-none ms-1">Create Account</a>
                    </p>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-5 text-muted small">
            &copy; <?= date('Y') ?> DQSSA Modern Queueing • ISPM Project
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>