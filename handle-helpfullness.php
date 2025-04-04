<?php
session_start();
header('Content-Type: application/json');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get CSRF token from header
$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
if (!isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['review_id']) || !isset($input['is_helpful'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$review_id = $input['review_id'];
$is_helpful = (int)$input['is_helpful'];

// Check user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tenant') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$user_id = $_SESSION['user_id'];

// Include database connection (do not include header.php or footer.php)
require __DIR__ . '/config.php';

try {
    // Check if user already voted
    $stmt = $pdo->prepare("SELECT helpful_id FROM review_helpfulness WHERE review_id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already voted on this review']);
        exit;
    }

    // Insert vote
    $stmt = $pdo->prepare("INSERT INTO review_helpfulness (review_id, user_id, is_helpful) VALUES (?, ?, ?)");
    $stmt->execute([$review_id, $user_id, $is_helpful]);

    echo json_encode(['success' => true]);
    exit;
} catch (PDOException $e) {
    error_log('Helpfulness Vote Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error submitting vote']);
    exit;
}
?>
