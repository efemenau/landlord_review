<?php
$page_title = "TenantReview - Rate Your Landlord";
require_once 'header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 mb-4">Rate Your Rental Experience</h1>
                <p class="lead mb-4">Help others find responsible landlords and avoid bad rental experiences.</p>
                <div class="d-flex gap-3">
                    <a href="reviews.php" class="btn cta-button">Read Reviews</a>
                    <!-- <a href="submit-review.php" class="btn cta-button-outline">Write Review</a> -->
                </div>
            </div>
            <div class="col-md-6">
                <img src="assets/images/cover_2.jpeg" alt="Rental Experience" class="img-fluid rounded">
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <img src="assets/images/about_1.jpeg" alt="About Us" class="img-fluid rounded">
            </div>
            <div class="col-md-6">
                <h2 class="mb-4">About TenantReview</h2>
                <p class="lead">We're creating transparency in the rental market by enabling tenants to share their experiences with landlords.</p>
                <p>Our platform helps:</p>
                <ul class="list-unstyled">
                    <li class="mb-2">✅ Rate landlord responsiveness</li>
                    <li class="mb-2">✅ Review property maintenance</li>
                    <li class="mb-2">✅ Share rental experiences</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>