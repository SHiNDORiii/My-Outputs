<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hcc_schedule";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$student_number = $_SESSION['student_number'];
$email = $_SESSION['email'];
$course = $_SESSION['course'];
$year_level = $_SESSION['year_level'];

$current_date = date("F j, Y");
$current_day = date("l"); // Get current day name (Monday, Tuesday, etc.)
$current_time = date("H:i:s");

// Fetch today's schedule based on course, year level, and current day
$today_schedule_sql = "SELECT s.section_code, s.section_name, s.schedule_day, s.schedule_time_start, s.schedule_time_end, s.room, s.instructor, 
                              sub.subject_code, sub.subject_name, sub.units
                       FROM section s 
                       JOIN subjects sub ON s.subject_id = sub.subject_id 
                       WHERE (s.course = ? OR s.course = 'ANY' OR s.course LIKE CONCAT('%', ?, '%'))
                       AND s.year_level = ?
                       AND s.schedule_day = ?
                       ORDER BY s.schedule_time_start";

$stmt = $conn->prepare($today_schedule_sql);
$stmt->bind_param("ssss", $course, $course, $year_level, $current_day);
$stmt->execute();
$today_schedule_result = $stmt->get_result();

$today_schedules = [];
while ($row = $today_schedule_result->fetch_assoc()) {
    $today_schedules[] = $row;
}
$stmt->close();

// Calculate total subjects and hours for today
$total_today_subjects = count($today_schedules);
$total_today_hours = 0;
foreach ($today_schedules as $schedule) {
    if ($schedule['schedule_time_start'] && $schedule['schedule_time_end']) {
        $start = new DateTime($schedule['schedule_time_start']);
        $end = new DateTime($schedule['schedule_time_end']);
        $interval = $start->diff($end);
        $hours = $interval->h + ($interval->i / 60);
        $total_today_hours += $hours;
    }
}

// Find current ongoing class
$current_class = null;
$next_class = null;
foreach ($today_schedules as $index => $schedule) {
    $start_time = $schedule['schedule_time_start'];
    $end_time = $schedule['schedule_time_end'];
    
    if ($current_time >= $start_time && $current_time <= $end_time) {
        $current_class = $schedule;
        $current_class['index'] = $index;
    }
    
    // Find next upcoming class
    if ($current_time < $start_time && $next_class === null) {
        $next_class = $schedule;
    }
}
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
    .no-schedule-message {
      text-align: center;
      color: #8699ad;
      padding: 2rem;
    }
    
    .current-class-badge {
      background: #28a745;
      color: white;
      padding: 0.2rem 0.6rem;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 600;
      margin-left: 0.5rem;
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
            <span class="user-role"><?php echo htmlspecialchars($course . ' ' . $year_level); ?></span>
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
        <a href="view_schedule.php" class="menu-item">
          <i class="fas fa-calendar-alt"></i>
          <span>My Schedule</span>
        </a>
        <a href="#" class="menu-item">
          <i class="fas fa-book"></i>
          <span>Classes</span>
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
        <a href="index.php" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </div>
    </aside>

    <main class="main-content">
      <div class="welcome-header">
        <div>
          <h1>Welcome back, <?php echo htmlspecialchars($first_name); ?>! <span>👋</span></h1>
          <p class="welcome-date">Today is <?php echo $current_date; ?> · <?php echo $current_day; ?> | 2nd Semester 2025-2026</p>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">
            <i class="fas fa-book-open"></i>
          </div>
          <div class="stat-info">
            <h3><?php echo $total_today_subjects; ?></h3>
            <p>Classes Today</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-info">
            <h3><?php echo round($total_today_hours, 1); ?></h3>
            <p>Hours Today</p>
          </div>
        </div>
      </div>

      <div class="content-grid">
        <div class="left-column">
          <div class="card">
            <div class="card-header">
              <h2><i class="fas fa-calendar-day"></i> Today's Schedule - <?php echo $current_day; ?></h2>
              <a href="schedule.php" class="view-link">View Full Week <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="schedule-list">
              <?php if (count($today_schedules) > 0): ?>
                <?php foreach ($today_schedules as $index => $schedule): 
                    $start_time = date('g:i A', strtotime($schedule['schedule_time_start']));
                    $end_time = date('g:i A', strtotime($schedule['schedule_time_end']));
                    $is_current = ($current_class && $current_class['index'] == $index);
                    $is_next = ($next_class && $next_class == $schedule && !$is_current);
                ?>
                  <div class="schedule-item <?php echo $is_current ? 'current' : ''; ?>">
                    <div class="schedule-time">
                      <?php if ($is_current): ?>
                        <span class="time-badge">Now</span>
                      <?php elseif ($is_next): ?>
                        <span class="time-badge">Up next</span>
                      <?php endif; ?>
                      <span><?php echo $start_time . ' - ' . $end_time; ?></span>
                    </div>
                    <div class="schedule-details">
                      <h3>
                        <?php echo htmlspecialchars($schedule['subject_name']); ?>
                        <?php if ($schedule['section_name']): ?>
                          <small style="font-size: 0.8rem; color: #8699ad;">(<?php echo htmlspecialchars($schedule['section_name']); ?>)</small>
                        <?php endif; ?>
                      </h3>
                      <p><?php echo htmlspecialchars($schedule['instructor']); ?> · <?php echo htmlspecialchars($schedule['room']); ?></p>
                    </div>
                    <div class="schedule-status">
                      <?php if ($is_current): ?>
                        <span class="status-badge ongoing">Ongoing</span>
                      <?php elseif ($is_next): ?>
                        <span class="status-badge upcoming">Up next</span>
                      <?php else: ?>
                        <span class="units-badge"><?php echo $schedule['units']; ?> units</span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="no-schedule-message">
                  <i class="fas fa-calendar-check" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #2a5298;"></i>
                  <p>No classes scheduled for today! 🎉</p>
                  <p style="font-size: 0.9rem; margin-top: 0.5rem;">Enjoy your free day!</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="right-column">
          <div class="card">
            <div class="card-header">
              <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
              <a href="#" class="view-link">View All</a>
            </div>
            <div class="announcement-list">
              <div class="announcement-item">
                <div class="announcement-content">
                  <p style="text-align: center; color: #8699ad; padding: 2rem;">No Announcements</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>

<?php
$conn->close();
?>