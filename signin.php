<?php
session_start();

// Generate CSRF token for each request
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$page_title = "Sign In - TenantReview";
require_once 'header.php';
?>

<section class="signin-section py-5" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="signin-form-container p-4 p-sm-5 shadow rounded bg-white">
                    <div class="text-center mb-5">
                        <h2 class="mb-3">Welcome to TenantReview</h2>
                        <p class="text-muted">Sign in to continue your journey</p>
                    </div>
                    <!-- error handling -->
                    <?php if (isset($_SESSION['login_errors']) && !empty($_SESSION['login_errors'])) { ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php foreach ($_SESSION['login_errors'] as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['login_errors']); ?>
                    <?php } ?>

                    <form action="login-process.php" method="POST" class="needs-validation" novalidate>

                        <!-- Add CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control"
                                value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>" placeholder="Enter your email"
                                id="email" name="email" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control rounded-pill px-4"
                                id="password" name="password" required
                                placeholder="••••••••">
                            <div class="invalid-feedback">
                                Please enter your password
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="text-decoration-none text-primary">
                                Forgot Password?
                            </a>
                        </div>

                        <button type="submit" class="btn cta-button w-100 py-2 mb-3 rounded-pill">
                            Sign In
                        </button>

                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">Don't have an account?
                                <a href="signup.php" class="text-decoration-none fw-bold text-primary">
                                    Create account
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Clear stored form data after display
unset($_SESSION['form_data']);
unset($_SESSION['login_errors']);  // For signin.php
unset($_SESSION['signup_errors']); // For signup.php
?>

<?php require_once 'footer.php'; ?>