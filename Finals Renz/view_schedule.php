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
$course = $_SESSION['course'];
$year_level = $_SESSION['year_level'];

$current_date = date("F j, Y");

// Check if student has custom schedule
$check_custom_sql = "SELECT COUNT(*) as custom_count FROM schedule WHERE student_id = ?";
$check_stmt = $conn->prepare($check_custom_sql);
$check_stmt->bind_param("i", $student_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$custom_count = $check_result->fetch_assoc()['custom_count'];
$has_custom_schedule = ($custom_count > 0);
$check_stmt->close();

// Fetch student's schedule (custom if exists, otherwise default)
if ($has_custom_schedule) {
    // Fetch custom schedule from schedule table
    $schedule_sql = "SELECT s.section_id, s.section_code, s.section_name, s.year_level, s.schedule_day, s.schedule_time_start, s.schedule_time_end, 
                            s.room, s.instructor, sub.subject_id, sub.subject_code, sub.subject_name, sub.units, sub.description
                     FROM schedule sch
                     JOIN section s ON sch.section_id = s.section_id
                     JOIN subjects sub ON s.subject_id = sub.subject_id
                     WHERE sch.student_id = ?
                     ORDER BY FIELD(s.schedule_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), 
                              s.schedule_time_start";
    $stmt = $conn->prepare($schedule_sql);
    $stmt->bind_param("i", $student_id);
} else {
    // Fetch default schedule based on course and year level
    $schedule_sql = "SELECT s.section_id, s.section_code, s.section_name, s.year_level, s.schedule_day, s.schedule_time_start, s.schedule_time_end, 
                            s.room, s.instructor, sub.subject_id, sub.subject_code, sub.subject_name, sub.units, sub.description
                     FROM section s 
                     JOIN subjects sub ON s.subject_id = sub.subject_id 
                     WHERE (s.course = ? OR s.course = 'ANY' OR s.course LIKE CONCAT('%', ?, '%'))
                     AND s.year_level = ?
                     ORDER BY FIELD(s.schedule_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), 
                              s.schedule_time_start";
    $stmt = $conn->prepare($schedule_sql);
    $stmt->bind_param("sss", $course, $course, $year_level);
}

$stmt->execute();
$schedule_result = $stmt->get_result();

$schedule_by_day = [
    'Monday' => [],
    'Tuesday' => [],
    'Wednesday' => [],
    'Thursday' => [],
    'Friday' => [],
    'Saturday' => [],
    'Sunday' => []
];

$all_schedules = [];
$total_units = 0;

while ($row = $schedule_result->fetch_assoc()) {
    $day = $row['schedule_day'];
    if (isset($schedule_by_day[$day])) {
        $schedule_by_day[$day][] = $row;
        $all_schedules[] = $row;
        $total_units += $row['units'];
    }
}
$stmt->close();

// Calculate total hours per week
$total_hours = 0;
foreach ($all_schedules as $schedule) {
    if ($schedule['schedule_time_start'] && $schedule['schedule_time_end']) {
        $start = new DateTime($schedule['schedule_time_start']);
        $end = new DateTime($schedule['schedule_time_end']);
        $interval = $start->diff($end);
        $hours = $interval->h + ($interval->i / 60);
        $total_hours += $hours;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Holy Cross College Pampanga · My Schedule</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="homepage.css">
  <style>
    .schedule-table {
      width: 100%;
      background: white;
      border-radius: 1rem;
      overflow-x: auto;
    }
    
    .schedule-table table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .schedule-table th {
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      color: white;
      padding: 1rem;
      text-align: left;
      font-weight: 600;
    }
    
    .schedule-table td {
      padding: 1rem;
      border-bottom: 1px solid #e2eaf2;
      color: #2c3e50;
    }
    
    .schedule-table tr:hover {
      background: #f9fcff;
    }
    
    .day-section {
      margin-bottom: 2rem;
    }
    
    .day-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: #1e3c72;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 3px solid #2a5298;
      display: inline-block;
    }
    
    .no-schedule {
      text-align: center;
      color: #8699ad;
      padding: 3rem;
      font-style: italic;
    }
    
    .time-badge {
      background: #e2eaf2;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
      color: #1e3c72;
      display: inline-block;
    }
    
    .units-badge {
      background: #2a5298;
      color: white;
      padding: 0.2rem 0.6rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      display: inline-block;
    }
    
    .instructor-name {
      color: #54708f;
      font-size: 0.9rem;
    }
    
    .room-info {
      color: #2a5298;
      font-weight: 500;
      font-size: 0.9rem;
    }
    
    .subject-code {
      font-weight: 600;
      color: #1e3c72;
    }
    
    .edit-btn {
      background: linear-gradient(135deg, #28a745, #20c997);
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 60px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }
    
    .edit-btn:hover {
      background: linear-gradient(135deg, #218838, #1aa179);
      transform: scale(1.02);
      color: white;
    }
    
    .custom-badge {
      background: #17a2b8;
      color: white;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-left: 1rem;
    }
    
    .header-actions {
      display: flex;
      gap: 1rem;
      align-items: center;
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
            <span class="user-role">
              <?php echo htmlspecialchars($course . ' ' . $year_level); ?>
              <?php if ($has_custom_schedule): ?>
                <span class="custom-badge"><i class="fas fa-pencil-alt"></i> Custom Schedule</span>
              <?php endif; ?>
            </span>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="sidebar-menu">
        <a href="homepage.php" class="menu-item">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
        </a>
        <a href="view_schedule.php" class="menu-item active">
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
          <h1>My Schedule <span>📅</span></h1>
          <p class="welcome-date"><?php echo htmlspecialchars($course); ?> · <?php echo htmlspecialchars($year_level); ?> | 2nd Semester 2025-2026</p>
        </div>
        <div class="header-actions">
          <a href="edit_schedule.php" class="edit-btn">
            <i class="fas fa-edit"></i> Edit Schedule
          </a>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">
            <i class="fas fa-book"></i>
          </div>
          <div class="stat-info">
            <h3><?php echo count($all_schedules); ?></h3>
            <p>Total Subjects</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-info">
            <h3><?php echo round($total_hours, 1); ?></h3>
            <p>Hours per Week</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">
            <i class="fas fa-star"></i>
          </div>
          <div class="stat-info">
            <h3><?php echo $total_units; ?></h3>
            <p>Total Units</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2><i class="fas fa-calendar-week"></i> Weekly Schedule</h2>
        </div>
        
        <div class="schedule-table">
          <table>
            <thead>
              <tr>
                <th>Time</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
                <th>Sunday</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $time_slots = [
                '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', 
                '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'
              ];
              
              // Re-fetch for weekly view
              if ($has_custom_schedule) {
                  $stmt2 = $conn->prepare("SELECT s.schedule_day, s.schedule_time_start, sub.subject_code, sub.subject_name, s.room, sub.units
                                          FROM schedule sch
                                          JOIN section s ON sch.section_id = s.section_id
                                          JOIN subjects sub ON s.subject_id = sub.subject_id
                                          WHERE sch.student_id = ?");
                  $stmt2->bind_param("i", $student_id);
              } else {
                  $stmt2 = $conn->prepare("SELECT s.schedule_day, s.schedule_time_start, sub.subject_code, sub.subject_name, s.room, sub.units
                                          FROM section s 
                                          JOIN subjects sub ON s.subject_id = sub.subject_id 
                                          WHERE (s.course = ? OR s.course = 'ANY' OR s.course LIKE CONCAT('%', ?, '%'))
                                          AND s.year_level = ?");
                  $stmt2->bind_param("sss", $course, $course, $year_level);
              }
              $stmt2->execute();
              $weekly_result = $stmt2->get_result();
              
              $schedule_by_day_time = [];
              while ($row = $weekly_result->fetch_assoc()) {
                  $day = $row['schedule_day'];
                  $start_hour = date('H:00', strtotime($row['schedule_time_start']));
                  $schedule_by_day_time[$day][$start_hour] = $row;
              }
              $stmt2->close();
              
              foreach ($time_slots as $time) {
                  echo "<tr>";
                  echo "<td><span class='time-badge'>" . date('g:i A', strtotime($time)) . "</span></td>";
                  
                  $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                  foreach ($days as $day) {
                      echo "<td>";
                      if (isset($schedule_by_day_time[$day][$time])) {
                          $class = $schedule_by_day_time[$day][$time];
                          echo "<strong>" . htmlspecialchars($class['subject_code']) . "</strong><br>";
                          echo "<small>" . htmlspecialchars($class['subject_name']) . "</small><br>";
                          echo "<small class='room-info'>" . htmlspecialchars($class['room']) . "</small><br>";
                          echo "<span class='units-badge'>" . htmlspecialchars($class['units']) . " units</span>";
                      } else {
                          echo "—";
                      }
                      echo "</td>";
                  }
                  echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
          <h2><i class="fas fa-list"></i> Detailed Schedule</h2>
        </div>
        
        <?php
        if (count($all_schedules) > 0) {
            foreach ($schedule_by_day as $day => $classes) {
                if (!empty($classes)) {
                    echo "<div class='day-section'>";
                    echo "<h3 class='day-title'><i class='fas fa-calendar-day'></i> " . $day . "</h3>";
                    echo "<div class='schedule-table'>";
                    echo "<table>";
                    echo "<thead><tr><th>Time</th><th>Subject Code</th><th>Subject Name</th><th>Room</th><th>Instructor</th><th>Units</th></tr></thead>";
                    echo "<tbody>";
                    
                    foreach ($classes as $class) {
                        $start_time = date('g:i A', strtotime($class['schedule_time_start']));
                        $end_time = date('g:i A', strtotime($class['schedule_time_end']));
                        echo "<tr>";
                        echo "<td><span class='time-badge'>" . $start_time . " - " . $end_time . "</span></td>";
                        echo "<td class='subject-code'>" . htmlspecialchars($class['subject_code']) . "</td>";
                        echo "<td><strong>" . htmlspecialchars($class['subject_name']) . "</strong><br><small>" . htmlspecialchars($class['description']) . "</small></td>";
                        echo "<td class='room-info'>" . htmlspecialchars($class['room']) . "</td>";
                        echo "<td class='instructor-name'>" . htmlspecialchars($class['instructor']) . "</td>";
                        echo "<td><span class='units-badge'>" . htmlspecialchars($class['units']) . "</span></td>";
                        echo "</tr>";
                    }
                    
                    echo "</tbody></table></div></div>";
                }
            }
        } else {
            echo "<div class='no-schedule'>";
            echo "<i class='fas fa-calendar-times' style='font-size: 3rem; margin-bottom: 1rem; display: block;'></i>";
            echo "<p>No schedule found for " . htmlspecialchars($course) . " - " . htmlspecialchars($year_level) . ".</p>";
            echo "<p style='font-size: 0.9rem;'>Click 'Edit Schedule' to add your classes.</p>";
            echo "</div>";
        }
        ?>
      </div>
    </main>
  </div>
</body>
</html>

<?php
$conn->close();
?>