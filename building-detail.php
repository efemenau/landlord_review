<?php
require_once 'header.php';

$building_id = $_GET['id'] ?? null;
$page_title = 'Building Details';

try {
    // Fetch building details
    $stmt = $pdo->prepare("SELECT b.*, 
                          AVG(r.overall_rating) AS avg_rating,
                          COUNT(r.review_id) AS total_reviews
                          FROM buildings b
                          LEFT JOIN reviews r ON b.building_id = r.building_id
                          WHERE b.building_id = ?
                          GROUP BY b.building_id");
    $stmt->execute([$building_id]);
    $building = $stmt->fetch();

    if (!$building) {
        throw new Exception("Building not found");
    }

    // Fetch approved reviews with helpfulness count
    $reviews_stmt = $pdo->prepare("SELECT r.*, 
                                  COUNT(h.helpful_id) AS helpful_count,
                                  t.name AS tenant_name
                                  FROM reviews r
                                  LEFT JOIN review_helpfulness h ON r.review_id = h.review_id
                                  LEFT JOIN tenants t ON r.tenant_id = t.tenant_id
                                  WHERE r.building_id = ? AND r.approved_status = 1
                                  GROUP BY r.review_id
                                  ORDER BY r.created_at DESC");
    $reviews_stmt->execute([$building_id]);
    $reviews = $reviews_stmt->fetchAll();

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container mt-5 pt-5 mb-4">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php else: ?>
        <!-- Building Header -->
        <div class="card mb-4 shadow">
            <div class="row g-0">
                <div class="col-md-4">
                    <?php if ($building['image_url']): ?>
                        <img src="<?= htmlspecialchars($building['image_url']) ?>" 
                            class="img-fluid rounded-start" alt="<?= htmlspecialchars($building['building_name']) ?>">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                            <i class="fas fa-building fa-5x"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h1 class="card-title"> <?= htmlspecialchars($building['building_name']) ?></h1>
                        <div class="d-flex align-items-center mb-3">
                            <div class="star-rating me-2" data-rating="<?= round($building['avg_rating'], 1) ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $building['avg_rating'] ? 'text-warning' : 'text-secondary' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-muted">(<?= $building['total_reviews'] ?> reviews)</span>
                        </div>
                        <p class="card-text">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($building['address']) ?><br>
                            <?= htmlspecialchars($building['city']) ?>, 
                            <?= htmlspecialchars($building['state_county']) ?>
                        </p>
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'tenant'): ?>
                            <a href="submit-review.php?building_id=<?= $building_id ?>" 
                               class="btn btn-primary">
                                Write a Review
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <h3 class="mb-4">Tenant Reviews</h3>
        
        <?php if (empty($reviews)): ?>
            <div class="alert alert-info">No reviews yet. Be the first to review this property!</div>
        <?php else: ?>
            <div class="row row-cols-1 g-4">
                <?php foreach ($reviews as $review): ?>
                    <div class="col">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5><?= htmlspecialchars($review['review_title']) ?></h5>
                                    <div class="text-muted">
                                        <?= date('M j, Y', strtotime($review['created_at'])) ?>
                                    </div>
                                </div>
                                <p class="card-text"> <?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted">
                                        Reviewed by <?= htmlspecialchars($review['tenant_name'] ?? 'Anonymous') ?>
                                    </div>
                                    <div class="helpful-section">
                                        <button class="btn btn-sm btn-outline-success helpful-btn" 
                                            data-review-id="<?= $review['review_id'] ?>">
                                            <i class="fas fa-thumbs-up"></i> 
                                            <span class="helpful-count"> <?= $review['helpful_count'] ?></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
// Handle helpful votes
const buttons = document.querySelectorAll('.helpful-btn');
buttons.forEach(button => {
    button.addEventListener('click', async (e) => {
        const reviewId = e.currentTarget.dataset.reviewId;
        const countSpan = e.currentTarget.querySelector('.helpful-count');
        
        try {
            const response = await fetch('handle-helpfulness.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                },
                body: JSON.stringify({
                    review_id: reviewId,
                    is_helpful: 1
                })
            });

            const result = await response.json();
            
            if (result.success) {
                countSpan.textContent = parseInt(countSpan.textContent) + 1;
                e.currentTarget.disabled = true;
            } else {
                alert(result.message || 'Error submitting vote');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error submitting vote');
        }
    });
});
</script>

<?php include 'footer.php'; ?>
