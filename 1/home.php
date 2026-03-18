<?php
session_start();
include("connection.php");

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Home | HCC Schedule Checker</title>
    <link rel="stylesheet" href="home-style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Navigation Bar with Logo -->
    <div class="navbar">
        <div class="nav-left">
            <img src="hcc-logo.png" alt="HCC Logo" class="logo">
            <h2>HCC Schedule Checker</h2>
        </div>
        <div class="nav-right">
            <span class="user-greeting">
                <i class='bx bxs-user-circle'></i>
                <?php echo $first_name . ' ' . $last_name; ?>
            </span>
            <a href="logout.php" class="logout-btn">
                <i class='bx bx-log-out'></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="home-container">
        <!-- Welcome Banner -->
        <div class="welcome-section">
            <h1>Welcome back, <?php echo $first_name . ' ' . $last_name; ?>!</h1>
            <p>Check your schedules and grades below</p>
        </div>

        <!-- Cards Grid -->
        <div class="cards-grid">
            <!-- Schedule Card -->
            <div class="card">
                <div class="card-icon">
                    <i class='bx bx-calendar'></i>
                </div>
                <h3>My Schedule</h3>
                <p>View your class schedule</p>
                <a href="#" class="card-link">View Schedule →</a>
            </div>

            <!-- Grades Card -->
            <div class="card">
                <div class="card-icon">
                    <i class='bx bx-book-open'></i>
                </div>
                <h3>My Grades</h3>
                <p>Check your grades</p>
                <a href="#" class="card-link">View Grades →</a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-section">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <div class="activity-item">
                    <i class='bx bx-check-circle'></i>
                    <div>
                        <p>Logged in successfully</p>
                        <small>Just now</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>