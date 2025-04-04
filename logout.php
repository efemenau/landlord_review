<?php
session_start();

// Unset all session values
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Regenerate session ID for new session
session_regenerate_id(true);

// Redirect to login with success message
$_SESSION['logout_success'] = true;
header('Location: signin.php');
exit;