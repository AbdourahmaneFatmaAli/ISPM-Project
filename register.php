<?php require_once 'includes/header.php'; ?>

<div class="row min-vh-100 align-items-center justify-content-center animate__animated animate__fadeIn pb-5 mt-4">
    <div class="col-md-5">
        <div class="card p-4 p-lg-5 Modern-Glass-Card border-0 shadow-2xl">
            <div class="text-center mb-5">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; border: 1px solid rgba(189, 32, 49, 0.3);">
                    <i class="fa-solid fa-user-plus text-primary fs-2"></i>
                </div>
                <h2 class="fw-bolder text-white mb-2">Create Account</h2>
                <p class="text-muted">Join the digital queue system today</p>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert bg-danger bg-opacity-10 text-danger border-0 small mb-4 animate__animated animate__shakeX">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="api/auth/register.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" name="name" class="form-control border-start-0" 
                               placeholder="John Doe" required autocomplete="name">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" name="email" class="form-control border-start-0" 
                               placeholder="name@example.com" required autocomplete="email">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label text-muted small fw-bold text-uppercase">Create Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-start-0" 
                               placeholder="••••••••" required autocomplete="new-password">
                    </div>
                    <div class="form-text text-muted smaller mt-2">Use at least 8 characters with numbers and symbols.</div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 shadow-lg mb-4">
                    CREATE STUDENT ACCOUNT <i class="fa-solid fa-chevron-right ms-2 small"></i>
                </button>

                <div class="text-center">
                    <p class="text-muted small mb-0">Already a member? 
                        <a href="login.php" class="text-primary fw-bold text-decoration-none ms-1">Sign In instead</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>