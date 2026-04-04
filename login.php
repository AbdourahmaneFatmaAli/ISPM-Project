<?php require_once 'includes/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle text-white mb-3" style="width: 60px; height: 60px; box-shadow: 0 4px 14px rgba(14,165,233,0.3)">
                <i class="fa-solid fa-user-lock fa-2x"></i>
            </div>
            <h2 class="fw-bold mb-1">Welcome Back</h2>
            <p class="text-muted">Sign in to your account</p>
        </div>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger border-0 shadow-sm"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success border-0 shadow-sm"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <form action="api/auth/login.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-muted"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="name@example.com" autocomplete="off" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-muted"><i class="fa-solid fa-key"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" autocomplete="new-password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Sign In <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i></button>
            <div class="text-center mt-3">
                <span class="text-muted">Don't have an account?</span> <a href="register.php" class="text-decoration-none fw-bold">Create one</a>
            </div>
        </form>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>