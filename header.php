<?php
session_start();
require_once 'config.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set secure session cookie parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

$page_title = "TenantReview - Rate Your Landlord"; // Default title
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
  <script src="https://kit.fontawesome.com/d67b16c3ad.js" crossorigin="anonymous"></script>
  <style>
    /* Additional modern styling tweaks */
    .navbar {
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .dropdown-menu {
      border-radius: 0.5rem;
      border: none;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .nav-link {
      font-weight: 500;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">TenantReview</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <!-- Always visible links -->
        <li class="nav-item">
          <a class="nav-link" href="reviews.php">Browse Reviews</a>
        </li>
        
        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Logged-in User Menu -->
          <!-- <li class="nav-item">
            <a class="nav-link" href="submit-review.php">Write Review</a>
          </li> -->
          
          <?php if ($_SESSION['user_type'] === 'landlord'): ?>
            <!-- Landlord Dropdown Menu -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                Landlord Panel
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="add-building.php">Add Building</a></li>
                <li><a class="dropdown-item" href="my-buildings.php">My Buildings</a></li>
              </ul>
            </li>
          <?php endif; ?>

          <!-- User Account Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="logout.php">
                  <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
              </li>
            </ul>
          </li>

        <?php else: ?>
          <!-- Guest Menu -->
          <li class="nav-item">
            <a class="nav-link" href="signin.php">
              <i class="fas fa-sign-in-alt"></i> Sign In
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="signup.php">
              <i class="fas fa-user-plus"></i> Sign Up
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
