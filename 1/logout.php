<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Logging Out | HCC Schedule Checker</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="2;url=index.php">
</head>
<body class="login-page">
    <div class="wrapper" style="text-align: center;">
        <h1>Goodbye! 👋</h1>
        <div class="message" style="background: #d4edda; color: #155724;">
            You have been successfully logged out.
        </div>
        <p>Redirecting to login page...</p>
    </div>
</body>
</html>