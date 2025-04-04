<?php
$page_title = "Sign Up - TenantReview";
require_once 'header.php';

// Use a default value for form data if not present
$form_data = $_SESSION['form_data'] ?? [
    'user_type' => 'tenant',
    'name'      => '',
    'email'     => '',
    'company'   => '',
    'phone'     => ''
];
?>
<section class="signup-section py-5" style="min-height: 100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10 col-lg-8">
        <div class="signup-card shadow-lg rounded-4 overflow-hidden">
          <!-- Sign-Up Header -->
          <div class="bg-light p-4 text-center">
            <h2>Create Your Account</h2>
          </div>

          <!-- Error Messages -->
          <?php if (isset($_SESSION['signup_errors']) && !empty($_SESSION['signup_errors'])): ?>
            <div class="alert alert-danger alert-dismissible fade show m-4">
              <?php foreach ($_SESSION['signup_errors'] as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
              <?php endforeach; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['signup_errors']); ?>
          <?php endif; ?>

          <!-- Registration Form -->
          <div class="p-4">
            <form id="registrationForm" action="signup-process.php" method="POST">
              <!-- CSRF Token Field -->
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

              <!-- User Type Selection -->
              <div class="mb-4">
                <h5 class="mb-2">I am a:</h5>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="user_type" id="user_type_tenant" value="tenant" <?= ($form_data['user_type'] === 'tenant') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="user_type_tenant">Tenant</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="user_type" id="user_type_landlord" value="landlord" <?= ($form_data['user_type'] === 'landlord') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="user_type_landlord">Landlord/Agent</label>
                </div>
              </div>

              <!-- Common Fields -->
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="text" class="form-control" id="name" name="name" required
                           value="<?= htmlspecialchars($form_data['name']) ?>">
                    <label for="name">Full Name</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" required
                           value="<?= htmlspecialchars($form_data['email']) ?>">
                    <label for="email">Email Address</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <label for="password">Password</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <label for="confirm_password">Confirm Password</label>
                  </div>
                </div>
              </div>

              <!-- Landlord-specific Fields -->
              <div class="landlord-fields mt-3 <?= ($form_data['user_type'] === 'landlord') ? '' : 'd-none' ?>">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="company" name="company"
                             value="<?= htmlspecialchars($form_data['company']) ?>">
                      <label for="company">Company Name (optional)</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="tel" class="form-control" id="phone" name="phone"
                             value="<?= htmlspecialchars($form_data['phone']) ?>">
                      <label for="phone">Contact Number</label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Terms Checkbox -->
              <div class="mt-4 px-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                  <label class="form-check-label" for="terms">
                    I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>
                  </label>
                </div>
              </div>

              <button type="submit" class="btn cta-button w-100 py-3 mt-4 rounded-pill">
                Create Account
              </button>

              <div class="text-center mt-4">
                <p class="text-muted mb-0">Already have an account?
                  <a href="signin.php" class="text-decoration-none fw-bold text-primary">Sign In</a>
                </p>
              </div>
            </form>
          </div> <!-- End Form Container -->
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Toggle landlord-specific fields based on user type selection
document.addEventListener('DOMContentLoaded', function () {
    const tenantRadio = document.getElementById('user_type_tenant');
    const landlordRadio = document.getElementById('user_type_landlord');
    const landlordFields = document.querySelector('.landlord-fields');
    const phoneInput = document.getElementById('phone');

    function toggleLandlordFields() {
        if (landlordRadio.checked) {
            landlordFields.classList.remove('d-none');
            phoneInput.required = true;
        } else {
            landlordFields.classList.add('d-none');
            phoneInput.required = false;
        }
    }

    tenantRadio.addEventListener('change', toggleLandlordFields);
    landlordRadio.addEventListener('change', toggleLandlordFields);
    // Initialize on load
    toggleLandlordFields();
});
</script>

<?php
unset($_SESSION['form_data']);
require_once 'footer.php';
?>
