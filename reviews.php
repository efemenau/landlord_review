<?php
session_start();
require_once 'config.php';

$page_title = "Search Buildings & Landlords - TenantReview";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$location_filter = isset($_GET['location']) ? trim($_GET['location']) : '';

try {
    // Base query with named parameters
    $query = "SELECT b.*, 
              AVG(r.overall_rating) AS avg_rating,
              COUNT(r.review_id) AS total_reviews
              FROM buildings b
              LEFT JOIN reviews r ON b.building_id = r.building_id
              WHERE 1=1";

    $params = [];

    // Add search conditions
    if (!empty($search_term)) {
        $query .= " AND (LOWER(b.building_name) LIKE LOWER(:search) OR LOWER(b.address) LIKE LOWER(:search))";
        $params[':search'] = "%$search_term%";
    }
    
    if (!empty($location_filter)) {
        $query .= " AND (b.city LIKE :location OR b.state_county LIKE :location)";
        $params[':location'] = "%$location_filter%";
    }

    $query .= " GROUP BY b.building_id
                ORDER BY avg_rating DESC, total_reviews DESC";

    // Get total results count
    $count_query = "SELECT COUNT(*) FROM ($query) AS subquery";
    $stmt_count = $pdo->prepare($count_query);
    $stmt_count->execute($params);
    $total_results = $stmt_count->fetchColumn();
    $total_pages = ceil($total_results / $per_page);

    // Add pagination to main query
    $offset = ($page - 1) * $per_page;
    $query .= " LIMIT :per_page OFFSET :offset";

    // Add pagination parameters
    $params[':per_page'] = $per_page;
    $params[':offset'] = $offset;

    // Execute main query
    $stmt = $pdo->prepare($query);

    // Bind parameters with proper types
    foreach ($params as $key => $value) {
        $param_type = PDO::PARAM_STR;
        if ($key === ':per_page' || $key === ':offset') {
            $param_type = PDO::PARAM_INT;
        }
        $stmt->bindValue($key, $value, $param_type);
    }

    $stmt->execute();
    $buildings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Search Error: " . $e->getMessage());
    $buildings = [];
    $total_pages = 0;
    $total_results = 0;
}

require_once 'header.php';
?>

<section class="search-section py-5">
    <div class="container">
        <!-- Search Form -->
        <div class="search-card mb-5 shadow-lg">
            <div class="card-body p-4">
                <form action="reviews.php" method="GET">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control form-control-lg"
                                    name="q" placeholder="Search buildings or landlords..."
                                    value="<?= htmlspecialchars($search_term) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <input type="text" class="form-control form-control-lg"
                                    name="location" placeholder="City or State..."
                                    value="<?= htmlspecialchars($location_filter) ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <?php if (!empty($buildings)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($buildings as $building): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-img-top position-relative" style="height: 200px; overflow: hidden;">
                                <?php if ($building['image_url']): ?>
                                    <img src="<?= htmlspecialchars($building['image_url']) ?>"
                                        alt="<?= htmlspecialchars($building['building_name']) ?>"
                                        class="img-fluid h-100 w-100 object-fit-cover">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                        <i class="fas fa-building fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= htmlspecialchars($building['building_name']) ?>
                                </h5>
                                <div class="card-text">
                                    <div class="mb-3">
                                        <?php $rating = round($building['avg_rating'], 1); ?>
                                        <div class="star-rating" data-rating="<?= $rating ?>">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= $i <= $rating ? 'text-warning' : 'text-secondary' ?>"></i>
                                            <?php endfor; ?>
                                            <span class="ms-2">(<?= $building['total_reviews'] ?> reviews)</span>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($building['address']) ?>
                                    </p>
                                    <p class="text-muted mb-0">
                                        <?= htmlspecialchars($building['city']) ?>,
                                        <?= htmlspecialchars($building['state_county']) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="building-detail.php?id=<?= $building['building_id'] ?>"
                                    class="btn btn-outline-primary w-100">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="reviews.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-info text-center">
                <?= empty($search_term) && empty($location_filter)
                    ? 'Browse popular buildings below'
                    : 'No results found. Try different search terms.' ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'footer.php'; ?>
