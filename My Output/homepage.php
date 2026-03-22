<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit();
}

// Get student information from session
$student_id = $_SESSION['student_id'];
$student_number = $_SESSION['student_number'];
$student_email = $_SESSION['email'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$year_level = $_SESSION['year_level'];

// Get current date
$current_date = date('F j, Y');
$current_day = date('l');
$current_time = date('H:i:s');

// Get student's schedule from database
$schedule_query = "SELECT 
    s.subject_code,
    s.subject_name,
    s.schedule_day,
    s.schedule_start,
    s.schedule_end,
    s.room,
    CONCAT(a.first_name, ' ', a.last_name) AS teacher_name
FROM subjects s
LEFT JOIN admin a ON s.teacher_id = a.admin_id
WHERE s.year_level = '$year_level'
    AND s.is_active = 1
ORDER BY FIELD(s.schedule_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), s.schedule_start";

$schedule_result = mysqli_query($conn, $schedule_query);

// Get today's schedule
$today_schedule = [];
$upcoming_schedule = [];
$current_class = null;

while ($row = mysqli_fetch_assoc($schedule_result)) {
    if ($row['schedule_day'] == $current_day) {
        $today_schedule[] = $row;
        
        // Check if class is currently ongoing
        if ($current_time >= $row['schedule_start'] && $current_time <= $row['schedule_end']) {
            $current_class = $row;
        }
    } else {
        // Get upcoming schedule for next days
        $upcoming_schedule[] = $row;
    }
}

// Get number of classes for the student
$total_classes = mysqli_num_rows(mysqli_query($conn, "SELECT subject_id FROM subjects WHERE year_level = '$year_level' AND is_active = 1"));

// Get total hours today
$total_hours_today = 0;
foreach ($today_schedule as $class) {
    $start = strtotime($class['schedule_start']);
    $end = strtotime($class['schedule_end']);
    $total_hours_today += ($end - $start) / 3600;
}

// Get announcements - with error handling
$announcements_result = false;
$table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'announcements'");
if (mysqli_num_rows($table_exists) > 0) {
    $announcements_query = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
    $announcements_result = mysqli_query($conn, $announcements_query);
}

// Get current semester
$semester = date('n') <= 6 ? '2nd Semester' : '1st Semester';
$school_year = date('Y') . '-' . (date('Y') + 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holy Cross College Pampanga · Dashboard</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="homepage.css">
    <style>
        .alert {
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .no-schedule {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .no-schedule i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-cross"></i>
                <div class="logo-text">
                    <span class="logo-main">HCC Schedule Portal</span>
                    <span class="logo-sub">Fides, Caritas, Libertas</span>
                </div>
            </div>
            
            <div class="nav-user">
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($first_name . '+' . $last_name); ?>&background=1e3c72&color=fff&size=40" alt="Profile" class="profile-img">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($year_level); ?> - Student</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-menu">
                <a href="homepage.php" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>My Schedule</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-book"></i>
                    <span>Classes</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Teachers</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Announcements</span>
                </a>
            </div>
            
            <div class="sidebar-footer">
                <div class="school-info">
                    <i class="fas fa-cross"></i>
                    <div>
                        <p class="school-name">Holy Cross College</p>
                        <p class="school-location">Pampanga</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn" style="text-decoration: none; display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem; color: #dc2626; border-radius: 12px; cursor: pointer;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div class="welcome-header">
                <div>
                    <h1>Welcome back, <?php echo htmlspecialchars($first_name); ?>! <span>👋</span></h1>
                    <p class="welcome-date">Today is <?php echo $current_date; ?> · <?php echo $semester; ?></p>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Schedule
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_classes; ?></h3>
                        <p>Current Classes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($total_hours_today, 1); ?></h3>
                        <p>Hours Today</p>
                    </div>
                </div>
            </div>

            <div class="content-grid">
                <div class="left-column">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-calendar-day"></i> Today's Schedule</h2>
                            <a href="#" class="view-link">View Full Week <i class="fas fa-arrow-right"></i></a>
                        </div>
                        
                        <?php if (count($today_schedule) > 0): ?>
                            <div class="schedule-list">
                                <?php foreach ($today_schedule as $class): ?>
                                    <?php
                                    $is_current = false;
                                    if ($current_class && $class['subject_code'] == $current_class['subject_code']) {
                                        $is_current = true;
                                    }
                                    ?>
                                    <div class="schedule-item <?php echo $is_current ? 'current' : ''; ?>">
                                        <div class="schedule-time">
                                            <?php if ($is_current): ?>
                                                <span class="time-badge">Now</span>
                                            <?php endif; ?>
                                            <span><?php echo date('h:i A', strtotime($class['schedule_start'])); ?> - <?php echo date('h:i A', strtotime($class['schedule_end'])); ?></span>
                                        </div>
                                        <div class="schedule-details">
                                            <h3><?php echo htmlspecialchars($class['subject_name']); ?></h3>
                                            <p><?php echo htmlspecialchars($class['teacher_name']); ?> · <?php echo htmlspecialchars($class['room']); ?></p>
                                        </div>
                                        <div class="schedule-status">
                                            <?php if ($is_current): ?>
                                                <span class="status-badge ongoing">Ongoing</span>
                                            <?php elseif (strtotime($class['schedule_start']) > strtotime($current_time)): ?>
                                                <span class="status-badge upcoming">Up next</span>
                                            <?php else: ?>
                                                <span class="status-badge completed">Completed</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-schedule">
                                <i class="fas fa-calendar-week"></i>
                                <p>No classes scheduled for today!</p>
                                <small>Enjoy your day off!</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-calendar-week"></i> Upcoming Schedule</h2>
                            <a href="#" class="view-link">View Full Week <i class="fas fa-arrow-right"></i></a>
                        </div>
                        
                        <?php if (count($upcoming_schedule) > 0): ?>
                            <div class="schedule-list">
                                <?php 
                                $displayed = array_slice($upcoming_schedule, 0, 3);
                                foreach ($displayed as $class): 
                                ?>
                                    <div class="schedule-item">
                                        <div class="schedule-time">
                                            <span><?php echo $class['schedule_day']; ?></span>
                                            <small><?php echo date('h:i A', strtotime($class['schedule_start'])); ?></small>
                                        </div>
                                        <div class="schedule-details">
                                            <h3><?php echo htmlspecialchars($class['subject_name']); ?></h3>
                                            <p><?php echo htmlspecialchars($class['teacher_name']); ?> · <?php echo htmlspecialchars($class['room']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-schedule">
                                <i class="fas fa-calendar-check"></i>
                                <p>No upcoming classes scheduled!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="right-column">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
                            <a href="#" class="view-link">Coming Soon</a>
                        </div>
                        <div class="announcement-list">
                            <div class="no-schedule" style="padding: 20px;">
                                <i class="fas fa-info-circle"></i>
                                <p>Announcements will be posted here soon.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-chart-line"></i> Class Progress</h2>
                        </div>
                        <div class="progress-list">
                            <div class="progress-item">
                                <div class="progress-label">
                                    <span>Overall Attendance</span>
                                    <span>89%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 89%"></div>
                                </div>
                            </div>
                            <div class="progress-item">
                                <div class="progress-label">
                                    <span>Assignments Completed</span>
                                    <span>76%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 76%"></div>
                                </div>
                            </div>
                            <div class="progress-item">
                                <div class="progress-label">
                                    <span>Semester Progress</span>
                                    <span>65%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 65%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                        </div>
                        <div class="quick-actions-grid">
                            <button class="quick-action-btn" onclick="window.location.href='#'">
                                <i class="fas fa-calendar-alt"></i>
                                <span>View Schedule</span>
                            </button>
                            <button class="quick-action-btn" onclick="window.location.href='#'">
                                <i class="fas fa-book"></i>
                                <span>My Classes</span>
                            </button>
                            <button class="quick-action-btn" onclick="window.location.href='#'">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Teachers</span>
                            </button>
                            <button class="quick-action-btn" onclick="window.location.href='logout.php'">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>