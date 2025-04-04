<?php
session_start();
require_once 'config.php';
$page_title = 'Submit Review';

// Check if user is logged in as tenant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tenant') {
  header('Location: signin.php');
  exit();
}

// Get building ID from query string
$building_id = $_GET['building_id'] ?? null;

// Fetch building details
try {
  $stmt = $pdo->prepare("SELECT * FROM buildings WHERE building_id = ?");
  $stmt->execute([$building_id]);
  $building = $stmt->fetch();

  if (!$building) {
    throw new Exception("Building not found");
  }
} catch (Exception $e) {
  $error = $e->getMessage();
}

// Check if user already reviewed this building
try {
  $stmt = $pdo->prepare("SELECT review_id FROM reviews WHERE tenant_id = ? AND building_id = ?");
  $stmt->execute([$_SESSION['user_id'], $building_id]);
  if ($stmt->fetch()) {
    $error = "You've already submitted a review for this building";
  }
} catch (PDOException $e) {
  error_log($e->getMessage());
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($error)) {
  // Validate CSRF token
  if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid CSRF token');
  }

  // Define the rating categories
  $ratingFields = [
    'cleanliness',
    'security',
    'payment_options',
    'maintenance',
    'payment_grace',
    'communication',
    'responsiveness'
  ];

  $data = [];
  foreach ($ratingFields as $field) {
    $value = (int) $_POST[$field];
    if ($value < 1 || $value > 5) {
      $error = "All ratings must be between 1 and 5";
      break;
    }
    $data[$field . '_rating'] = $value;
  }

  // Validate title and review text
  $data['review_title'] = trim(strip_tags($_POST['review_title']));
  $data['review_text']  = trim(strip_tags($_POST['review_text']));

  if (strlen($data['review_title']) < 10 || strlen($data['review_text']) < 50) {
    $error = "Review title must be at least 10 characters and review text 50 characters";
  }

  if (!isset($error)) {
    try {
      $stmt = $pdo->prepare("INSERT INTO reviews 
        (tenant_id, landlord_id, building_id, review_title, review_text,
         cleanliness_rating, security_rating, payment_options_rating,
         maintenance_rating, payment_grace_rating, communication_rating,
         responsiveness_rating)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
      );

      $stmt->execute([
        $_SESSION['user_id'],
        $building['landlord_id'],
        $building_id,
        $data['review_title'],
        $data['review_text'],
        $data['cleanliness_rating'],
        $data['security_rating'],
        $data['payment_options_rating'],
        $data['maintenance_rating'],
        $data['payment_grace_rating'],
        $data['communication_rating'],
        $data['responsiveness_rating']
      ]);

      $_SESSION['success'] = "Review submitted successfully!";
      header("Location: building-details.php?id=$building_id");
      exit();
    } catch (PDOException $e) {
      $error = "Error submitting review: " . $e->getMessage();
    }
  }
}
require_once 'header.php';
?>
<div class="container mt-5 pt-5">
  <div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-primary text-white py-3">
          <h3 class="mb-0 text-center">Review <?= htmlspecialchars($building['building_name'] ?? '') ?></h3>
        </div>
        <div class="card-body p-4">
          <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <?php if (isset($building)): ?>
            <form method="POST" id="reviewForm" class="needs-validation" novalidate>
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

              <!-- Rating Categories -->
              <div class="mb-4">
                <h5 class="mb-3">Rate the following (1-5 stars):</h5>
                <?php
                // Rating labels
                $ratingLabels = [
                  'cleanliness'      => 'Cleanliness',
                  'security'         => 'Security',
                  'payment_options'  => 'Payment Options',
                  'maintenance'      => 'Maintenance',
                  'payment_grace'    => 'Payment Grace',
                  'communication'    => 'Communication',
                  'responsiveness'   => 'Responsiveness'
                ];
                foreach ($ratingLabels as $field => $label): ?>
                  <div class="mb-3">
                    <label class="form-label fw-bold"><?= htmlspecialchars($label) ?></label>
                    <div class="rating-stars">
                      <?php 
                      // Use RTL approach so the highest star is on the left
                      for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="<?= $field ?>-<?= $i ?>" name="<?= $field ?>" value="<?= $i ?>" required>
                        <label for="<?= $field ?>-<?= $i ?>">&#9733;</label>
                      <?php endfor; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Review Title -->
              <div class="mb-3">
                <label for="review_title" class="form-label fw-bold">Review Title</label>
                <input type="text" class="form-control" id="review_title" name="review_title" required minlength="10" placeholder="Enter a title for your review">
              </div>

              <!-- Review Text -->
              <div class="mb-4">
                <label for="review_text" class="form-label fw-bold">Your Experience</label>
                <textarea class="form-control" id="review_text" name="review_text" rows="5" required minlength="50" placeholder="Describe your experience"></textarea>
              </div>

              <button type="submit" class="btn btn-primary btn-lg w-100">Submit Review</button>
            </form>
          <?php else: ?>
            <div class="alert alert-danger">Building not found</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Custom CSS for Rating Stars -->
<style>
.rating-stars {
    direction: rtl;             /* Reverse the order for correct highlighting */
    unicode-bidi: bidi-override; /* Ensure proper ordering of stars */
    display: inline-block;
}
.rating-stars input {
    display: none;
}
.rating-stars label {
    font-size: 1.8rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease-in-out;
}

/* Highlight stars when selected */
.rating-stars input:checked ~ label,
.rating-stars label:hover,
.rating-stars label:hover ~ label {
    color: #ffc107;
}
</style>
<?php include 'footer.php'; ?>
