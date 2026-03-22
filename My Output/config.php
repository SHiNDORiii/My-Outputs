<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hcc_schedule';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Check if remember token exists and auto-login
if (!isset($_SESSION['student_id']) && isset($_COOKIE['remember_token'])) {
    $token = mysqli_real_escape_string($conn, $_COOKIE['remember_token']);
    $sql = "SELECT student_id, student_number, email, first_name, last_name, year_level FROM students WHERE remember_token = '$token' AND is_active = 1 AND enrollment_status = 'Enrolled'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $student = mysqli_fetch_assoc($result);
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_number'] = $student['student_number'];
        $_SESSION['email'] = $student['email'];
        $_SESSION['first_name'] = $student['first_name'];
        $_SESSION['last_name'] = $student['last_name'];
        $_SESSION['year_level'] = $student['year_level'];
        
        header('Location: dashboard.php');
        exit();
    }
}
?>