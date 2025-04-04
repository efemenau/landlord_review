<?php
require_once 'header.php';
$page_title = 'My Buildings';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: signin.php');
    exit();
}

// Handle building deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_building'])) {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $building_id = $_POST['building_id'];
    
    try {
        // Verify ownership before deletion
        $stmt = $pdo->prepare("SELECT image_url FROM buildings WHERE building_id = ? AND landlord_id = ?");
        $stmt->execute([$building_id, $_SESSION['user_id']]);
        $building = $stmt->fetch();

        if ($building) {
            // Delete database record
            $deleteStmt = $pdo->prepare("DELETE FROM buildings WHERE building_id = ?");
            $deleteStmt->execute([$building_id]);

            // Delete associated image
            if ($building['image_url'] && file_exists($building['image_url'])) {
                unlink($building['image_url']);
            }

            $_SESSION['success'] = "Building deleted successfully";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting building: " . $e->getMessage();
    }
    
    header("Location: my-buildings.php");
    exit();
}

// Fetch user's buildings
try {
    $stmt = $pdo->prepare("SELECT * FROM buildings WHERE landlord_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $buildings = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching buildings: " . $e->getMessage();
}
?>

<div class="container mt-5 pt-5">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">My Buildings</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (empty($buildings)): ?>
                <div class="alert alert-info">
                    You haven't added any buildings yet. <a href="add-building.php" class="alert-link">Add your first building</a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                    <?php foreach ($buildings as $building): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm">
                                <?php if ($building['image_url']): ?>
                                    <img src="<?= htmlspecialchars($building['image_url']) ?>" 
                                         class="card-img-top building-image" 
                                         alt="<?= htmlspecialchars($building['building_name']) ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="fas fa-building fa-3x"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($building['building_name']) ?></h5>
                                    <p class="card-text">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($building['address']) ?><br>
                                        <?= htmlspecialchars($building['city']) ?>, 
                                        <?= htmlspecialchars($building['state_county']) ?>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        Added: <?= date('M j, Y', strtotime($building['created_at'])) ?>
                                    </p>
                                </div>
                                
                                <div class="card-footer bg-white">
                                    <div class="d-flex justify-content-between">
                                        <a href="edit-building.php?id=<?= $building['building_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        
                                        <form method="POST" class="delete-form">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="building_id" value="<?= $building['building_id'] ?>">
                                            <button type="submit" name="delete_building" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this building?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .building-image {
        height: 200px;
        object-fit: cover;
        object-position: center;
    }
    
    .delete-form {
        display: inline-block;
    }
</style>

<?php include 'footer.php'; ?>