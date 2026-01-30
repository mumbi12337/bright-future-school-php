<?php
require_once 'includes/Auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth();
$auth->logout();

// The Auth::logout() method already handles the redirect to login.php
// If we reach here, there was an error, so let's redirect manually
header('Location: login.php');
exit;
?>