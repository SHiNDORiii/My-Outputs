<?php
$host = "localhost";  
$username = "root";  
$password = "";  
$database = "hcc_schedule_db";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8");

?>