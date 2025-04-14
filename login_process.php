<?php
session_start();

// Validate CSRF Token First
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['login_errors'] = ['Invalid CSRF token'];
    header('Location: signin.php');
    exit;
}

// Destroy token after verification
unset($_SESSION['csrf_token']);

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: reviews.php');
    exit();
}

// Include database connection
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signin.php');
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

// Basic validation
$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($password)) {
    $errors[] = 'Password is required';
}

if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['form_data'] = ['email' => $email]; 
    header('Location: signin.php');
    exit;
}

try {
    // Check both tables simultaneously
    $stmt = $pdo->prepare("
        SELECT 'tenant' AS user_type, tenant_id AS id, name, email, password 
        FROM tenants 
        WHERE email = ?
        UNION ALL
        SELECT 'landlord' AS user_type, landlord_id AS id, name, email, password 
        FROM landlords 
        WHERE email = ?
    ");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();

    // Verify credentials
    if ($user && password_verify($password, $user['password'])) {
        // Update session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type']; // 'tenant' or 'landlord'
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        
        // Regenerate session ID
        session_regenerate_id(true);

        // Redirect to dashboard
        header('Location: reviews.php');
        exit;
    }

    // Invalid credentials
    $_SESSION['login_errors'] = ['Invalid email or password'];
    $_SESSION['form_data'] = ['email' => $email];
    header('Location: signin.php');
    exit;

} catch (PDOException $e) {
    error_log('Login Error: ' . $e->getMessage());
    $_SESSION['login_errors'] = ['Login failed. Please try again.'];
    header('Location: signin.php');
    exit;
}
?>