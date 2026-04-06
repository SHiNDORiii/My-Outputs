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

$message = "";
$message_type = "";

// Get ALL available sections from ALL year levels
$sections_sql = "SELECT s.section_id, s.section_code, s.section_name, s.year_level, s.schedule_day, s.schedule_time_start, s.schedule_time_end, 
                        s.room, s.instructor, sub.subject_id, sub.subject_code, sub.subject_name, sub.units
                 FROM section s
                 JOIN subjects sub ON s.subject_id = sub.subject_id
                 WHERE s.course = ? OR s.course = 'ANY' OR s.course LIKE CONCAT('%', ?, '%')
                 ORDER BY FIELD(s.year_level, '1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'),
                          FIELD(s.schedule_day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), 
                          s.schedule_time_start";

$stmt = $conn->prepare($sections_sql);
$stmt->bind_param("ss", $course, $course);
$stmt->execute();
$sections_result = $stmt->get_result();
$available_sections = [];
$sections_by_id = [];
while ($row = $sections_result->fetch_assoc()) {
    $available_sections[] = $row;
    $sections_by_id[$row['section_id']] = $row;
}
$stmt->close();

// Get student's current schedule
$current_schedule_sql = "SELECT section_id FROM schedule WHERE student_id = ?";
$stmt = $conn->prepare($current_schedule_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$current_schedule_result = $stmt->get_result();
$current_sections = [];
while ($row = $current_schedule_result->fetch_assoc()) {
    $current_sections[] = $row['section_id'];
}
$stmt->close();

// Function to check for schedule conflicts
function checkConflicts($selected_section_ids, $sections_by_id) {
    $conflicts = [];
    $schedule_map = [];
    
    foreach ($selected_section_ids as $section_id) {
        if (!isset($sections_by_id[$section_id])) continue;
        
        $section = $sections_by_id[$section_id];
        $day = $section['schedule_day'];
        $start = $section['schedule_time_start'];
        $end = $section['schedule_time_end'];
        
        if (!isset($schedule_map[$day])) {
            $schedule_map[$day] = [];
        }
        
        // Check for conflicts with existing schedules on the same day
        foreach ($schedule_map[$day] as $existing) {
            $existing_start = $existing['start'];
            $existing_end = $existing['end'];
            
            // Check if time ranges overlap
            if (($start >= $existing_start && $start < $existing_end) ||
                ($end > $existing_start && $end <= $existing_end) ||
                ($start <= $existing_start && $end >= $existing_end)) {
                $conflicts[] = [
                    'section1' => $section,
                    'section2' => $existing['section'],
                    'day' => $day
                ];
            }
        }
        
        $schedule_map[$day][] = [
            'start' => $start,
            'end' => $end,
            'section' => $section
        ];
    }
    
    return $conflicts;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_sections = isset($_POST['sections']) ? $_POST['sections'] : [];
    
    // Check for conflicts
    $conflicts = checkConflicts($selected_sections, $sections_by_id);
    
    if (count($conflicts) > 0) {
        $message = "Schedule conflict detected! Cannot save because of the following conflicts:<br><ul>";
        foreach ($conflicts as $conflict) {
            $start1 = date('g:i A', strtotime($conflict['section1']['schedule_time_start']));
            $end1 = date('g:i A', strtotime($conflict['section1']['schedule_time_end']));
            $start2 = date('g:i A', strtotime($conflict['section2']['schedule_time_start']));
            $end2 = date('g:i A', strtotime($conflict['section2']['schedule_time_end']));
            $message .= "<li><strong>" . htmlspecialchars($conflict['section1']['subject_code']) . " - " . htmlspecialchars($conflict['section1']['subject_name']) . "</strong> (" . $start1 . " - " . $end1 . ") conflicts with <strong>" . htmlspecialchars($conflict['section2']['subject_code']) . " - " . htmlspecialchars($conflict['section2']['subject_name']) . "</strong> (" . $start2 . " - " . $end2 . ") on <strong>" . $conflict['day'] . "</strong></li>";
        }
        $message .= "</ul>";
        $message_type = "error";
    } else {
        // Delete existing schedule
        $delete_sql = "DELETE FROM schedule WHERE student_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $student_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Insert new schedule with section_id and subject_id
        if (count($selected_sections) > 0) {
            $insert_sql = "INSERT INTO schedule (student_id, section_id, subject_id) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            
            foreach ($selected_sections as $section_id) {
                if (isset($sections_by_id[$section_id])) {
                    $subject_id = $sections_by_id[$section_id]['subject_id'];
                    $insert_stmt->bind_param("iii", $student_id, $section_id, $subject_id);
                    $insert_stmt->execute();
                }
            }
            $insert_stmt->close();
            
            $message = "Schedule saved successfully! " . count($selected_sections) . " subjects added.";
            $message_type = "success";
            
            // Refresh current sections
            $current_sections = $selected_sections;
        } else {
            $message = "Schedule cleared. No subjects selected.";
            $message_type = "success";
        }
    }
}

// Group sections by year level for better organization
$sections_by_year = [
    '1st Year' => [],
    '2nd Year' => [],
    '3rd Year' => [],
    '4th Year' => [],
    '5th Year' => []
];

foreach ($available_sections as $section) {
    $year = $section['year_level'];
    if (isset($sections_by_year[$year])) {
        $sections_by_year[$year][] = $section;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Holy Cross College Pampanga · Edit Schedule</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="homepage.css">
  <style>
    .sections-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .section-card {
      background: #f9fcff;
      border: 2px solid #e2eaf2;
      border-radius: 1rem;
      padding: 1rem;
      transition: all 0.2s;
      cursor: pointer;
    }
    
    .section-card:hover {
      border-color: #2a5298;
      background: white;
    }
    
    .section-card.selected {
      border-color: #28a745;
      background: #d4edda;
    }
    
    .section-card.conflict {
      border-color: #dc3545;
      background: #f8d7da;
      opacity: 0.7;
    }
    
    .section-card input {
      margin-right: 0.5rem;
    }
    
    .section-title {
      font-weight: 600;
      color: #1e3c72;
      margin-bottom: 0.5rem;
    }
    
    .section-details {
      font-size: 0.85rem;
      color: #54708f;
      margin-left: 1.5rem;
    }
    
    .section-details p {
      margin: 0.25rem 0;
    }
    
    .form-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      justify-content: flex-end;
    }
    
    .save-btn {
      background: linear-gradient(135deg, #28a745, #20c997);
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 60px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .save-btn:hover {
      background: linear-gradient(135deg, #218838, #1aa179);
      transform: scale(1.02);
    }
    
    .cancel-btn {
      background: #6c757d;
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 60px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }
    
    .cancel-btn:hover {
      background: #5a6268;
    }
    
    .message {
      padding: 1rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: 500;
    }
    
    .message.success {
      background: #d4edda;
      border: 2px solid #28a745;
      color: #155724;
    }
    
    .message.error {
      background: #f8d7da;
      border: 2px solid #dc3545;
      color: #721c24;
      text-align: left;
    }
    
    .message.error ul {
      margin-top: 0.5rem;
      margin-bottom: 0;
      padding-left: 1.5rem;
    }
    
    .year-section {
      margin-bottom: 2rem;
    }
    
    .year-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: #1e3c72;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 3px solid #2a5298;
      display: inline-block;
    }
    
    .year-badge {
      background: #2a5298;
      color: white;
      padding: 0.2rem 0.6rem;
      border-radius: 20px;
      font-size: 0.7rem;
      margin-left: 0.5rem;
      vertical-align: middle;
    }
    
    .select-all-btn {
      background: #6c757d;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      cursor: pointer;
      margin-left: 1rem;
      transition: all 0.2s;
    }
    
    .select-all-btn:hover {
      background: #5a6268;
    }
    
    .filter-section {
      margin-bottom: 1.5rem;
      display: flex;
      gap: 1rem;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .search-box {
      flex: 1;
      padding: 0.8rem;
      border: 1.5px solid #e2eaf2;
      border-radius: 60px;
      font-size: 0.9rem;
      outline: none;
    }
    
    .search-box:focus {
      border-color: #2a5298;
    }
    
    .filter-btn {
      background: #1e3c72;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 60px;
      cursor: pointer;
    }
    
    .conflict-warning {
      color: #dc3545;
      font-size: 0.8rem;
      margin-top: 0.5rem;
      display: none;
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
        <a href="homepage.php" class="menu-item">
          <i class="fas fa-home"></i>
          <span>Dashboard</span>
        </a>
        <a href="schedule.php" class="menu-item active">
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
          <h1>Edit My Schedule <span>✏️</span></h1>
          <p class="welcome-date">Select your classes (you can choose from ANY year level)</p>
          <p style="color: #dc3545; font-size: 0.85rem; margin-top: 0.5rem;">
            <i class="fas fa-exclamation-triangle"></i> Note: Subjects with conflicting schedules will be highlighted in red and cannot be saved together
          </p>
        </div>
      </div>

      <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
          <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <h2><i class="fas fa-list-check"></i> Available Classes</h2>
          <p>Browse all subjects from all year levels. Select the classes you want to enroll in.</p>
        </div>
        
        <div class="filter-section">
          <input type="text" id="searchInput" class="search-box" placeholder="Search by subject code, name, or instructor...">
          <button class="filter-btn" onclick="clearFilters()">Clear Filters</button>
        </div>
        
        <form method="POST" action="" id="scheduleForm" onsubmit="return validateConflictsBeforeSubmit()">
          <div id="conflictWarning" class="message error" style="display: none; margin-bottom: 1rem;">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Schedule Conflicts Detected!</strong>
            <div id="conflictList"></div>
            <p style="margin-top: 0.5rem;">Please resolve these conflicts before saving.</p>
          </div>
          
          <?php foreach ($sections_by_year as $year => $sections): ?>
            <?php if (!empty($sections)): ?>
              <div class="year-section" data-year="<?php echo $year; ?>">
                <div>
                  <h3 class="year-title">
                    <?php echo $year; ?>
                    <span class="year-badge"><?php echo count($sections); ?> subjects</span>
                  </h3>
                  <button type="button" class="select-all-btn" onclick="selectAllByYear('<?php echo $year; ?>')">Select All</button>
                  <button type="button" class="select-all-btn" onclick="deselectAllByYear('<?php echo $year; ?>')" style="background: #dc3545;">Deselect All</button>
                </div>
                <div class="sections-grid">
                  <?php foreach ($sections as $section): 
                      $start_time = date('g:i A', strtotime($section['schedule_time_start']));
                      $end_time = date('g:i A', strtotime($section['schedule_time_end']));
                      $is_selected = in_array($section['section_id'], $current_sections);
                  ?>
                    <div class="section-card <?php echo $is_selected ? 'selected' : ''; ?>" 
                         data-year="<?php echo $year; ?>"
                         data-section-id="<?php echo $section['section_id']; ?>"
                         data-day="<?php echo $section['schedule_day']; ?>"
                         data-start="<?php echo $section['schedule_time_start']; ?>"
                         data-end="<?php echo $section['schedule_time_end']; ?>"
                         data-subject="<?php echo strtolower($section['subject_name'] . ' ' . $section['subject_code'] . ' ' . $section['instructor']); ?>"
                         onclick="toggleSection(this, <?php echo $section['section_id']; ?>)">
                      <div>
                        <input type="checkbox" name="sections[]" value="<?php echo $section['section_id']; ?>" id="section_<?php echo $section['section_id']; ?>" <?php echo $is_selected ? 'checked' : ''; ?> onclick="event.stopPropagation(); checkConflicts();">
                        <label for="section_<?php echo $section['section_id']; ?>" class="section-title">
                          <strong><?php echo htmlspecialchars($section['subject_code']); ?> - <?php echo htmlspecialchars($section['subject_name']); ?></strong>
                        </label>
                      </div>
                      <div class="section-details">
                        <p><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($section['schedule_day']); ?> | <?php echo $start_time; ?> - <?php echo $end_time; ?></p>
                        <p><i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($section['instructor']); ?></p>
                        <p><i class="fas fa-door-open"></i> <?php echo htmlspecialchars($section['room']); ?></p>
                        <p><i class="fas fa-star"></i> <?php echo htmlspecialchars($section['units']); ?> units</p>
                        <p><i class="fas fa-graduation-cap"></i> <strong>Year Level:</strong> <?php echo $year; ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
          
          <div class="form-actions">
            <a href="schedule.php" class="cancel-btn">Cancel</a>
            <button type="submit" class="save-btn">Save Schedule</button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    // Store all sections data
    const sectionsData = {};
    <?php foreach ($available_sections as $section): ?>
      sectionsData[<?php echo $section['section_id']; ?>] = {
        id: <?php echo $section['section_id']; ?>,
        code: '<?php echo addslashes($section['subject_code']); ?>',
        name: '<?php echo addslashes($section['subject_name']); ?>',
        day: '<?php echo $section['schedule_day']; ?>',
        start: '<?php echo $section['schedule_time_start']; ?>',
        end: '<?php echo $section['schedule_time_end']; ?>',
        startTime: '<?php echo date('g:i A', strtotime($section['schedule_time_start'])); ?>',
        endTime: '<?php echo date('g:i A', strtotime($section['schedule_time_end'])); ?>'
      };
    <?php endforeach; ?>
    
    function toggleSection(card, sectionId) {
      const checkbox = card.querySelector('input[type="checkbox"]');
      checkbox.checked = !checkbox.checked;
      
      if (checkbox.checked) {
        card.classList.add('selected');
      } else {
        card.classList.remove('selected');
      }
      
      checkConflicts();
    }
    
    function selectAllByYear(year) {
      const cards = document.querySelectorAll(`.section-card[data-year="${year}"]`);
      cards.forEach(card => {
        const checkbox = card.querySelector('input[type="checkbox"]');
        if (!checkbox.checked) {
          checkbox.checked = true;
          card.classList.add('selected');
        }
      });
      checkConflicts();
    }
    
    function deselectAllByYear(year) {
      const cards = document.querySelectorAll(`.section-card[data-year="${year}"]`);
      cards.forEach(card => {
        const checkbox = card.querySelector('input[type="checkbox"]');
        if (checkbox.checked) {
          checkbox.checked = false;
          card.classList.remove('selected');
        }
      });
      checkConflicts();
    }
    
    function checkConflicts() {
      const checkboxes = document.querySelectorAll('input[name="sections[]"]:checked');
      const selectedSections = [];
      const conflicts = [];
      const scheduleMap = {};
      
      checkboxes.forEach(cb => {
        const sectionId = parseInt(cb.value);
        const section = sectionsData[sectionId];
        if (section) {
          selectedSections.push(section);
        }
      });
      
      // Check for conflicts
      selectedSections.forEach(section => {
        if (!scheduleMap[section.day]) {
          scheduleMap[section.day] = [];
        }
        
        // Check against existing schedules on the same day
        scheduleMap[section.day].forEach(existing => {
          if (timeOverlaps(section.start, section.end, existing.start, existing.end)) {
            conflicts.push({
              section1: section,
              section2: existing
            });
          }
        });
        
        scheduleMap[section.day].push(section);
      });
      
      // Highlight conflicting cards
      const allCards = document.querySelectorAll('.section-card');
      allCards.forEach(card => {
        card.classList.remove('conflict');
      });
      
      if (conflicts.length > 0) {
        const conflictWarning = document.getElementById('conflictWarning');
        const conflictList = document.getElementById('conflictList');
        conflictList.innerHTML = '<ul>';
        
        conflicts.forEach(conflict => {
          conflictList.innerHTML += `<li><strong>${conflict.section1.code} - ${conflict.section1.name}</strong> (${conflict.section1.startTime} - ${conflict.section1.endTime}) conflicts with <strong>${conflict.section2.code} - ${conflict.section2.name}</strong> (${conflict.section2.startTime} - ${conflict.section2.endTime}) on <strong>${conflict.section1.day}</strong></li>`;
          
          // Highlight conflicting cards
          const card1 = document.querySelector(`.section-card[data-section-id="${conflict.section1.id}"]`);
          const card2 = document.querySelector(`.section-card[data-section-id="${conflict.section2.id}"]`);
          if (card1) card1.classList.add('conflict');
          if (card2) card2.classList.add('conflict');
        });
        
        conflictList.innerHTML += '</ul>';
        conflictWarning.style.display = 'block';
        
        // Scroll to conflict warning
        conflictWarning.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        return false;
      } else {
        document.getElementById('conflictWarning').style.display = 'none';
        return true;
      }
    }
    
    function timeOverlaps(start1, end1, start2, end2) {
      return (start1 >= start2 && start1 < end2) ||
             (end1 > start2 && end1 <= end2) ||
             (start1 <= start2 && end1 >= end2);
    }
    
    function validateConflictsBeforeSubmit() {
      const hasConflicts = document.getElementById('conflictWarning').style.display === 'block';
      if (hasConflicts) {
        alert('Cannot save schedule! Please resolve all schedule conflicts before saving.');
        return false;
      }
      return true;
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', function() {
      const searchTerm = this.value.toLowerCase();
      const cards = document.querySelectorAll('.section-card');
      const yearSections = document.querySelectorAll('.year-section');
      
      cards.forEach(card => {
        const subjectText = card.getAttribute('data-subject');
        if (subjectText.includes(searchTerm) || searchTerm === '') {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
      
      // Hide empty year sections
      yearSections.forEach(section => {
        const visibleCards = section.querySelectorAll('.section-card[style="display: block"], .section-card:not([style])');
        const actualVisible = Array.from(visibleCards).filter(card => card.style.display !== 'none');
        if (actualVisible.length === 0 && searchTerm !== '') {
          section.style.display = 'none';
        } else {
          section.style.display = 'block';
        }
      });
    });
    
    function clearFilters() {
      searchInput.value = '';
      const cards = document.querySelectorAll('.section-card');
      const yearSections = document.querySelectorAll('.year-section');
      
      cards.forEach(card => {
        card.style.display = 'block';
      });
      
      yearSections.forEach(section => {
        section.style.display = 'block';
      });
    }
    
    // Initial conflict check on page load
    document.addEventListener('DOMContentLoaded', function() {
      checkConflicts();
    });
  </script>
</body>
</html>

<?php
$conn->close();
?>