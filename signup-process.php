<?php
session_start();

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['signup_errors'] = ['Invalid CSRF token'];
    header('Location: signup.php');
    exit;
}
unset($_SESSION['csrf_token']);

require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit;
}

// Normalize and retrieve inputs
$user_type = strtolower(trim($_POST['user_type'] ?? 'tenant'));
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

// Validate user type using radio button input
if (!in_array($user_type, ['tenant', 'landlord'])) {
    $errors[] = 'Invalid user type';
}

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters';
}

// Landlord-specific validations
$phone = '';
$company = '';
if ($user_type === 'landlord') {
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($phone) || !preg_match('/^\d{10,15}$/', $phone)) {
        $errors[] = 'Valid phone number (10-15 digits) required for landlords';
    }
}

// Retain form data on error
$_SESSION['form_data'] = [
    'user_type' => $user_type,
    'name'      => $name,
    'email'     => $email,
    'company'   => $company,
    'phone'     => $phone
];

if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors;
    header('Location: signup.php');
    exit;
}

try {
    $table = ($user_type === 'tenant') ? 'tenants' : 'landlords';
    $stmt = $pdo->prepare("SELECT email FROM $table WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Email already registered';
        $_SESSION['signup_errors'] = $errors;
        header('Location: signup.php');
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($user_type === 'tenant') {
        $stmt = $pdo->prepare("INSERT INTO tenants (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO landlords (name, email, password, phone, company) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $phone, $company]);
    }

    $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $user_id = ($user_type === 'tenant') ? $user['tenant_id'] : $user['landlord_id'];

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_type'] = $user_type;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;

    unset($_SESSION['form_data']);
    $_SESSION['signup_success'] = 'Registration successful. Please log in.';
    header('Location: signin.php');
    exit;
} catch (PDOException $e) {
    error_log('Signup Error: ' . $e->getMessage());
    $_SESSION['signup_errors'] = ['Registration failed. Please try again.'];
    header('Location: signup.php');
    exit;
}
?>
