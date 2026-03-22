<?php
session_start();
require_once 'config.php';

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
?>