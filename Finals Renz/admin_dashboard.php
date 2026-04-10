<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
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

$admin_name = $_SESSION['admin_fullname'];

// Handle delete section
if (isset($_GET['delete_section'])) {
    $section_id = $_GET['delete_section'];
    $delete_sql = "DELETE FROM section WHERE section_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $section_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: admin_dashboard.php?msg=deleted");
    exit();
}

// Handle delete subject
if (isset($_GET['delete_subject'])) {
    $subject_id = $_GET['delete_subject'];
    $del_cur_sql = "DELETE FROM curriculum WHERE subject_id = ?";
    $del_cur_stmt = $conn->prepare($del_cur_sql);
    $del_cur_stmt->bind_param("i", $subject_id);
    $del_cur_stmt->execute();
    $del_cur_stmt->close();
    $delete_sql = "DELETE FROM subjects WHERE subject_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $subject_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: admin_dashboard.php?msg=deleted");
    exit();
}

// Handle delete curriculum
if (isset($_GET['delete_curriculum'])) {
    $curriculum_id = $_GET['delete_curriculum'];
    $delete_sql = "DELETE FROM curriculum WHERE curriculum_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $curriculum_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: admin_dashboard.php?msg=deleted");
    exit();
}

// Handle delete student
if (isset($_GET['delete_student'])) {
    $student_id = $_GET['delete_student'];
    $delete_sql = "DELETE FROM student WHERE student_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $student_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: admin_dashboard.php?msg=deleted");
    exit();
}

// Handle add/edit subject
$subject_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_subject'])) {
    $subject_id = $_POST['subject_id'] ?? '';
    $subject_code = mysqli_real_escape_string($conn, $_POST['subject_code']);
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $units = mysqli_real_escape_string($conn, $_POST['units']);
    
    if (empty($subject_id)) {
        $insert_sql = "INSERT INTO subjects (subject_code, subject_name, description, units) 
                       VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sssi", $subject_code, $subject_name, $description, $units);
        
        if ($stmt->execute()) {
            $subject_id = $stmt->insert_id;
            $subject_message = "Subject added successfully!";
        } else {
            $subject_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $update_sql = "UPDATE subjects SET subject_code=?, subject_name=?, description=?, units=? WHERE subject_id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssii", $subject_code, $subject_name, $description, $units, $subject_id);
        
        if ($stmt->execute()) {
            $subject_message = "Subject updated successfully!";
        } else {
            $subject_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle add/edit curriculum
$curriculum_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_curriculum'])) {
    $curriculum_id = $_POST['curriculum_id'] ?? '';
    $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    
    if (empty($curriculum_id)) {
        $insert_sql = "INSERT INTO curriculum (subject_id, course, year_level, semester) 
                       VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("isss", $subject_id, $course, $year_level, $semester);
        
        if ($stmt->execute()) {
            $curriculum_message = "Curriculum entry added successfully!";
        } else {
            $curriculum_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $update_sql = "UPDATE curriculum SET subject_id=?, course=?, year_level=?, semester=? WHERE curriculum_id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("isssi", $subject_id, $course, $year_level, $semester, $curriculum_id);
        
        if ($stmt->execute()) {
            $curriculum_message = "Curriculum entry updated successfully!";
        } else {
            $curriculum_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle add/edit section
$section_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_section'])) {
    $section_id = $_POST['section_id'] ?? '';
    $section_code = mysqli_real_escape_string($conn, $_POST['section_code']);
    $section_name = mysqli_real_escape_string($conn, $_POST['section_name']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $schedule_day = mysqli_real_escape_string($conn, $_POST['schedule_day']);
    $schedule_time_start = mysqli_real_escape_string($conn, $_POST['schedule_time_start']);
    $schedule_time_end = mysqli_real_escape_string($conn, $_POST['schedule_time_end']);
    $room = mysqli_real_escape_string($conn, $_POST['room']);
    $instructor = mysqli_real_escape_string($conn, $_POST['instructor']);
    $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
    
    if (empty($section_id)) {
        $insert_sql = "INSERT INTO section (section_code, section_name, year_level, semester, course, academic_year, schedule_day, schedule_time_start, schedule_time_end, room, instructor, subject_id) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sssssssssssi", $section_code, $section_name, $year_level, $semester, $course, $academic_year, $schedule_day, $schedule_time_start, $schedule_time_end, $room, $instructor, $subject_id);
        
        if ($stmt->execute()) {
            $section_message = "Section added successfully!";
        } else {
            $section_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $update_sql = "UPDATE section SET section_code=?, section_name=?, year_level=?, semester=?, course=?, academic_year=?, schedule_day=?, schedule_time_start=?, schedule_time_end=?, room=?, instructor=?, subject_id=? WHERE section_id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssssssssii", $section_code, $section_name, $year_level, $semester, $course, $academic_year, $schedule_day, $schedule_time_start, $schedule_time_end, $room, $instructor, $subject_id, $section_id);
        
        if ($stmt->execute()) {
            $section_message = "Section updated successfully!";
        } else {
            $section_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get search parameters
$section_search = isset($_GET['section_search']) ? $_GET['section_search'] : '';
$subject_search = isset($_GET['subject_search']) ? $_GET['subject_search'] : '';
$curriculum_search = isset($_GET['curriculum_search']) ? $_GET['curriculum_search'] : '';
$student_search = isset($_GET['student_search']) ? $_GET['student_search'] : '';

// Get all subjects with search
$subjects_sql = "SELECT * FROM subjects";
if (!empty($subject_search)) {
    $subjects_sql .= " WHERE subject_code LIKE '%$subject_search%' OR subject_name LIKE '%$subject_search%'";
}
$subjects_sql .= " ORDER BY subject_id DESC";
$subjects_result = $conn->query($subjects_sql);

// Get all curriculum entries with search
$curriculum_sql = "SELECT c.*, s.subject_code, s.subject_name, s.units 
                   FROM curriculum c 
                   JOIN subjects s ON c.subject_id = s.subject_id";
if (!empty($curriculum_search)) {
    $curriculum_sql .= " WHERE s.subject_code LIKE '%$curriculum_search%' OR s.subject_name LIKE '%$curriculum_search%' OR c.course LIKE '%$curriculum_search%'";
}
$curriculum_sql .= " ORDER BY FIELD(c.year_level, '1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'), c.semester, c.course";
$curriculum_result = $conn->query($curriculum_sql);

// Get all sections with search
$sections_sql = "SELECT s.*, sub.subject_code, sub.subject_name FROM section s LEFT JOIN subjects sub ON s.subject_id = sub.subject_id";
if (!empty($section_search)) {
    $sections_sql .= " WHERE s.section_code LIKE '%$section_search%' OR s.section_name LIKE '%$section_search%' OR s.course LIKE '%$section_search%' OR s.instructor LIKE '%$section_search%' OR sub.subject_name LIKE '%$section_search%'";
}
$sections_sql .= " ORDER BY s.section_id DESC";
$sections_result = $conn->query($sections_sql);

// Get all students with search
$students_sql = "SELECT * FROM student";
if (!empty($student_search)) {
    $students_sql .= " WHERE student_number LIKE '%$student_search%' OR first_name LIKE '%$student_search%' OR last_name LIKE '%$student_search%' OR email LIKE '%$student_search%' OR course LIKE '%$student_search%'";
}
$students_sql .= " ORDER BY student_id DESC";
$students_result = $conn->query($students_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard · HCC Schedule Portal</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }
    
    body {
      background: #f0f2f5;
    }
    
    .admin-header {
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .logo {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .logo i {
      font-size: 1.5rem;
    }
    
    .admin-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .logout-btn {
      background: rgba(255,255,255,0.2);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      text-decoration: none;
      transition: all 0.2s;
    }
    
    .logout-btn:hover {
      background: rgba(255,255,255,0.3);
    }
    
    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 2rem;
      border-bottom: 2px solid #e2eaf2;
      flex-wrap: wrap;
    }
    
    .tab-btn {
      background: none;
      border: none;
      padding: 1rem 2rem;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      color: #54708f;
      transition: all 0.2s;
    }
    
    .tab-btn.active {
      color: #2a5298;
      border-bottom: 3px solid #2a5298;
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
    
    .card {
      background: white;
      border-radius: 1rem;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid #e2eaf2;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #28a745, #20c997);
      color: white;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 50px;
      cursor: pointer;
      font-weight: 500;
    }
    
    .btn-danger {
      background: #dc3545;
      color: white;
      border: none;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      cursor: pointer;
      font-size: 0.8rem;
    }
    
    .btn-edit {
      background: #ffc107;
      color: #856404;
      border: none;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      cursor: pointer;
      font-size: 0.8rem;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    th, td {
      padding: 0.75rem;
      text-align: left;
      border-bottom: 1px solid #e2eaf2;
    }
    
    th {
      background: #f9fcff;
      font-weight: 600;
      color: #1e3c72;
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 0.3rem;
      font-weight: 500;
      color: #1e3c72;
    }
    
    .form-group input, .form-group select, .form-group textarea {
      width: 100%;
      padding: 0.6rem;
      border: 1.5px solid #e2eaf2;
      border-radius: 12px;
      font-size: 0.9rem;
    }
    
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }
    
    .message {
      padding: 0.8rem;
      border-radius: 12px;
      margin-bottom: 1rem;
      background: #d4edda;
      border: 1px solid #28a745;
      color: #155724;
    }
    
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    
    .modal-content {
      background: white;
      border-radius: 1rem;
      padding: 2rem;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
    }
    
    .close {
      cursor: pointer;
      font-size: 1.5rem;
    }
    
    .search-bar {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }
    
    .search-bar input {
      flex: 1;
      padding: 0.6rem 1rem;
      border: 1.5px solid #e2eaf2;
      border-radius: 50px;
      font-size: 0.9rem;
    }
    
    .search-bar button {
      background: #2a5298;
      color: white;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 50px;
      cursor: pointer;
    }
    
    .badge {
      display: inline-block;
      padding: 0.2rem 0.5rem;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 600;
    }
    
    .badge-info {
      background: #17a2b8;
      color: white;
    }
    
    .badge-warning {
      background: #ffc107;
      color: #856404;
    }
    
    .table-container {
      overflow-x: auto;
    }
    
    .action-buttons {
      display: flex;
      gap: 0.3rem;
      flex-wrap: wrap;
    }
  </style>
</head>
<body>
  <div class="admin-header">
    <div class="logo">
      <i class="fas fa-cross"></i>
      <div>
        <strong>HCC Schedule Portal</strong><br>
        <small>Admin Dashboard</small>
      </div>
    </div>
    <div class="admin-info">
      <span><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin_name); ?></span>
      <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
  
  <div class="container">
    <?php if (isset($_GET['msg'])): ?>
      <div class="message">Operation completed successfully!</div>
    <?php endif; ?>
    <?php if (!empty($subject_message)): ?>
      <div class="message"><?php echo $subject_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($curriculum_message)): ?>
      <div class="message"><?php echo $curriculum_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($section_message)): ?>
      <div class="message"><?php echo $section_message; ?></div>
    <?php endif; ?>
    
    <div class="tabs">
      <button class="tab-btn active" onclick="showTab('subjects', event)">📖 Subjects</button>
      <button class="tab-btn" onclick="showTab('curriculum', event)">📚 Curriculum</button>
      <button class="tab-btn" onclick="showTab('sections', event)">📅 Sections</button>
      <button class="tab-btn" onclick="showTab('students', event)">👨‍🎓 Students</button>
    </div>
    
    <!-- Subjects Tab -->
    <div id="subjects-tab" class="tab-content active">
      <div class="card">
        <div class="card-header">
          <h2><i class="fas fa-book"></i> Manage Subjects</h2>
          <button class="btn-primary" onclick="openSubjectModal()"><i class="fas fa-plus"></i> Add Subject</button>
        </div>
        
        <form method="GET" action="" class="search-bar">
          <input type="text" name="subject_search" placeholder="Search by subject code or name..." value="<?php echo htmlspecialchars($subject_search); ?>">
          <button type="submit"><i class="fas fa-search"></i> Search</button>
          <?php if (!empty($subject_search)): ?>
            <a href="admin_dashboard.php" style="background: #6c757d; color: white; padding: 0.6rem 1.2rem; border-radius: 50px; text-decoration: none;"><i class="fas fa-times"></i> Clear</a>
          <?php endif; ?>
        </form>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Description</th>
                <th>Units</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($subjects_result && $subjects_result->num_rows > 0): ?>
                <?php while ($row = $subjects_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $row['subject_id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['subject_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><span class="badge badge-info"><?php echo $row['units']; ?> units</span></td>
                    <td class="action-buttons">
                      <button class="btn-edit" onclick='editSubject(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i> Edit</button>
                      <button class="btn-danger" onclick="deleteSubject(<?php echo $row['subject_id']; ?>)"><i class="fas fa-trash"></i> Del</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 2rem;">No subjects found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Curriculum Tab -->
    <div id="curriculum-tab" class="tab-content">
      <div class="card">
        <div class="card-header">
          <h2><i class="fas fa-graduation-cap"></i> Curriculum</h2>
          <button class="btn-primary" onclick="openCurriculumModal()"><i class="fas fa-plus"></i> Add to Curriculum</button>
        </div>
        
        <form method="GET" action="" class="search-bar">
          <input type="text" name="curriculum_search" placeholder="Search by subject, course..." value="<?php echo htmlspecialchars($curriculum_search); ?>">
          <button type="submit"><i class="fas fa-search"></i> Search</button>
          <?php if (!empty($curriculum_search)): ?>
            <a href="admin_dashboard.php" style="background: #6c757d; color: white; padding: 0.6rem 1.2rem; border-radius: 50px; text-decoration: none;"><i class="fas fa-times"></i> Clear</a>
          <?php endif; ?>
        </form>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Units</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Semester</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($curriculum_result && $curriculum_result->num_rows > 0): ?>
                <?php while ($row = $curriculum_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $row['curriculum_id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['subject_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td><span class="badge badge-info"><?php echo $row['units']; ?> units</span></td>
                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                    <td><span class="badge badge-warning"><?php echo htmlspecialchars($row['year_level']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['semester']); ?></td>
                    <td class="action-buttons">
                      <button class="btn-edit" onclick='editCurriculum(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i> Edit</button>
                      <button class="btn-danger" onclick="deleteCurriculum(<?php echo $row['curriculum_id']; ?>)"><i class="fas fa-trash"></i> Del</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" style="text-align: center; padding: 2rem;">No curriculum entries found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Sections Tab -->
    <div id="sections-tab" class="tab-content">
      <div class="card">
        <div class="card-header">
          <h2><i class="fas fa-calendar-alt"></i> Manage Sections</h2>
          <button class="btn-primary" onclick="openSectionModal()"><i class="fas fa-plus"></i> Add Section</button>
        </div>
        
        <form method="GET" action="" class="search-bar">
          <input type="text" name="section_search" placeholder="Search by section code, name, course, instructor, or subject..." value="<?php echo htmlspecialchars($section_search); ?>">
          <button type="submit"><i class="fas fa-search"></i> Search</button>
          <?php if (!empty($section_search)): ?>
            <a href="admin_dashboard.php" style="background: #6c757d; color: white; padding: 0.6rem 1.2rem; border-radius: 50px; text-decoration: none;"><i class="fas fa-times"></i> Clear</a>
          <?php endif; ?>
        </form>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Section Code</th>
                <th>Section Name</th>
                <th>Year Level</th>
                <th>Course</th>
                <th>Subject</th>
                <th>Day</th>
                <th>Time</th>
                <th>Room</th>
                <th>Instructor</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($sections_result && $sections_result->num_rows > 0): ?>
                <?php while ($row = $sections_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $row['section_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['section_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['section_name']); ?></td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($row['year_level']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['schedule_day']); ?></td>
                    <td><?php echo date('g:i A', strtotime($row['schedule_time_start'])) . ' - ' . date('g:i A', strtotime($row['schedule_time_end'])); ?></td>
                    <td><?php echo htmlspecialchars($row['room']); ?></td>
                    <td><?php echo htmlspecialchars($row['instructor']); ?></td>
                    <td class="action-buttons">
                      <button class="btn-edit" onclick='editSection(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i> Edit</button>
                      <button class="btn-danger" onclick="deleteSection(<?php echo $row['section_id']; ?>)"><i class="fas fa-trash"></i> Del</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="11" style="text-align: center; padding: 2rem;">No sections found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Students Tab -->
    <div id="students-tab" class="tab-content">
      <div class="card">
        <div class="card-header">
          <h2><i class="fas fa-users"></i> Registered Students</h2>
        </div>
        
        <form method="GET" action="" class="search-bar">
          <input type="text" name="student_search" placeholder="Search by student number, name, email, or course..." value="<?php echo htmlspecialchars($student_search); ?>">
          <button type="submit"><i class="fas fa-search"></i> Search</button>
          <?php if (!empty($student_search)): ?>
            <a href="admin_dashboard.php" style="background: #6c757d; color: white; padding: 0.6rem 1.2rem; border-radius: 50px; text-decoration: none;"><i class="fas fa-times"></i> Clear</a>
          <?php endif; ?>
        </form>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Student Number</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Year Level</th>
                <th>Course</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($students_result && $students_result->num_rows > 0): ?>
                <?php while ($row = $students_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $row['student_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['student_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($row['year_level']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                    <td class="action-buttons">
                      <button class="btn-danger" onclick="deleteStudent(<?php echo $row['student_id']; ?>)"><i class="fas fa-trash"></i> Delete</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" style="text-align: center; padding: 2rem;">No students found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Subject Modal -->
  <div id="subjectModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="subjectModalTitle">Add Subject</h3>
        <span class="close" onclick="closeSubjectModal()">&times;</span>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="subject_id" id="subject_id_field">
        <div class="form-row">
          <div class="form-group">
            <label>Subject Code</label>
            <input type="text" name="subject_code" id="subject_code_field" required>
          </div>
          <div class="form-group">
            <label>Subject Name</label>
            <input type="text" name="subject_name" id="subject_name_field" required>
          </div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" id="description_field" rows="2"></textarea>
        </div>
        <div class="form-group">
          <label>Units</label>
          <input type="number" name="units" id="units_field" required min="1">
        </div>
        <button type="submit" name="save_subject" class="btn-primary" style="width:100%; margin-top:1rem;">Save Subject</button>
      </form>
    </div>
  </div>
  
  <!-- Curriculum Modal -->
  <div id="curriculumModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="curriculumModalTitle">Add to Curriculum</h3>
        <span class="close" onclick="closeCurriculumModal()">&times;</span>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="curriculum_id" id="curriculum_id">
        <div class="form-group">
          <label>Subject</label>
          <select name="subject_id" id="curriculum_subject_id" required>
            <option value="">Select Subject</option>
            <?php
            $subjects_select = $conn->query("SELECT subject_id, subject_code, subject_name, units FROM subjects ORDER BY subject_code");
            while ($subj = $subjects_select->fetch_assoc()):
            ?>
              <option value="<?php echo $subj['subject_id']; ?>"><?php echo $subj['subject_code'] . ' - ' . $subj['subject_name'] . ' (' . $subj['units'] . ' units)'; ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Course</label>
          <input type="text" name="course" id="curriculum_course" placeholder="e.g., BSIT, BSCS" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Year Level</label>
            <select name="year_level" id="curriculum_year_level" required>
              <option value="1st Year">1st Year</option>
              <option value="2nd Year">2nd Year</option>
              <option value="3rd Year">3rd Year</option>
              <option value="4th Year">4th Year</option>
              <option value="5th Year">5th Year</option>
            </select>
          </div>
          <div class="form-group">
            <label>Semester</label>
            <select name="semester" id="curriculum_semester" required>
              <option value="1st Semester">1st Semester</option>
              <option value="2nd Semester">2nd Semester</option>
              <option value="Summer">Summer</option>
            </select>
          </div>
        </div>
        <button type="submit" name="save_curriculum" class="btn-primary" style="width:100%; margin-top:1rem;">Save to Curriculum</button>
      </form>
    </div>
  </div>
  
  <!-- Section Modal -->
  <div id="sectionModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="sectionModalTitle">Add Section</h3>
        <span class="close" onclick="closeSectionModal()">&times;</span>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="section_id" id="section_id">
        <div class="form-row">
          <div class="form-group">
            <label>Section Code</label>
            <input type="text" name="section_code" id="section_code" required>
          </div>
          <div class="form-group">
            <label>Section Name</label>
            <input type="text" name="section_name" id="section_name" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Year Level</label>
            <select name="year_level" id="year_level" required>
              <option value="1st Year">1st Year</option>
              <option value="2nd Year">2nd Year</option>
              <option value="3rd Year">3rd Year</option>
              <option value="4th Year">4th Year</option>
              <option value="5th Year">5th Year</option>
            </select>
          </div>
          <div class="form-group">
            <label>Semester</label>
            <select name="semester" id="semester" required>
              <option value="1st Semester">1st Semester</option>
              <option value="2nd Semester">2nd Semester</option>
              <option value="Summer">Summer</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Course</label>
            <input type="text" name="course" id="course" placeholder="e.g., BSIT, BSCS, ANY" required>
          </div>
          <div class="form-group">
            <label>Academic Year</label>
            <input type="text" name="academic_year" id="academic_year" placeholder="e.g., 2025-2026" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Schedule Day</label>
            <select name="schedule_day" id="schedule_day" required>
              <option value="Monday">Monday</option>
              <option value="Tuesday">Tuesday</option>
              <option value="Wednesday">Wednesday</option>
              <option value="Thursday">Thursday</option>
              <option value="Friday">Friday</option>
              <option value="Saturday">Saturday</option>
              <option value="Sunday">Sunday</option>
            </select>
          </div>
          <div class="form-group">
            <label>Subject</label>
            <select name="subject_id" id="subject_id_select" required>
              <option value="">Select Subject</option>
              <?php
              $subjects_select2 = $conn->query("SELECT subject_id, subject_code, subject_name FROM subjects ORDER BY subject_code");
              while ($subj = $subjects_select2->fetch_assoc()):
              ?>
                <option value="<?php echo $subj['subject_id']; ?>"><?php echo $subj['subject_code'] . ' - ' . $subj['subject_name']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Start Time</label>
            <input type="time" name="schedule_time_start" id="schedule_time_start" required>
          </div>
          <div class="form-group">
            <label>End Time</label>
            <input type="time" name="schedule_time_end" id="schedule_time_end" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Room</label>
            <input type="text" name="room" id="room" required>
          </div>
          <div class="form-group">
            <label>Instructor</label>
            <input type="text" name="instructor" id="instructor" required>
          </div>
        </div>
        <button type="submit" name="save_section" class="btn-primary" style="width:100%; margin-top:1rem;">Save Section</button>
      </form>
    </div>
  </div>

  <script>
    function showTab(tabName, event) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
      
      document.getElementById(tabName + '-tab').classList.add('active');
      if (event && event.target) {
        event.target.classList.add('active');
      }
    }
    
    function openSubjectModal() {
      document.getElementById('subjectModal').style.display = 'flex';
      document.getElementById('subjectModalTitle').innerText = 'Add Subject';
      document.getElementById('subject_id_field').value = '';
      document.getElementById('subject_code_field').value = '';
      document.getElementById('subject_name_field').value = '';
      document.getElementById('description_field').value = '';
      document.getElementById('units_field').value = '';
    }
    
    function editSubject(data) {
      document.getElementById('subjectModal').style.display = 'flex';
      document.getElementById('subjectModalTitle').innerText = 'Edit Subject';
      document.getElementById('subject_id_field').value = data.subject_id;
      document.getElementById('subject_code_field').value = data.subject_code;
      document.getElementById('subject_name_field').value = data.subject_name;
      document.getElementById('description_field').value = data.description;
      document.getElementById('units_field').value = data.units;
    }
    
    function deleteSubject(id) {
      if (confirm('Are you sure you want to delete this subject?')) {
        window.location.href = 'admin_dashboard.php?delete_subject=' + id;
      }
    }
    
    function openCurriculumModal() {
      document.getElementById('curriculumModal').style.display = 'flex';
      document.getElementById('curriculumModalTitle').innerText = 'Add to Curriculum';
      document.getElementById('curriculum_id').value = '';
      document.getElementById('curriculum_subject_id').value = '';
      document.getElementById('curriculum_course').value = '';
      document.getElementById('curriculum_year_level').value = '1st Year';
      document.getElementById('curriculum_semester').value = '2nd Semester';
    }
    
    function editCurriculum(data) {
      document.getElementById('curriculumModal').style.display = 'flex';
      document.getElementById('curriculumModalTitle').innerText = 'Edit Curriculum Entry';
      document.getElementById('curriculum_id').value = data.curriculum_id;
      document.getElementById('curriculum_subject_id').value = data.subject_id;
      document.getElementById('curriculum_course').value = data.course;
      document.getElementById('curriculum_year_level').value = data.year_level;
      document.getElementById('curriculum_semester').value = data.semester;
    }
    
    function deleteCurriculum(id) {
      if (confirm('Are you sure you want to delete this curriculum entry?')) {
        window.location.href = 'admin_dashboard.php?delete_curriculum=' + id;
      }
    }
    
    function openSectionModal() {
      document.getElementById('sectionModal').style.display = 'flex';
      document.getElementById('sectionModalTitle').innerText = 'Add Section';
      document.getElementById('section_id').value = '';
      document.getElementById('section_code').value = '';
      document.getElementById('section_name').value = '';
      document.getElementById('year_level').value = '1st Year';
      document.getElementById('semester').value = '2nd Semester';
      document.getElementById('course').value = '';
      document.getElementById('academic_year').value = '';
      document.getElementById('schedule_day').value = 'Monday';
      document.getElementById('schedule_time_start').value = '';
      document.getElementById('schedule_time_end').value = '';
      document.getElementById('room').value = '';
      document.getElementById('instructor').value = '';
      document.getElementById('subject_id_select').value = '';
    }
    
    function editSection(data) {
      document.getElementById('sectionModal').style.display = 'flex';
      document.getElementById('sectionModalTitle').innerText = 'Edit Section';
      document.getElementById('section_id').value = data.section_id;
      document.getElementById('section_code').value = data.section_code;
      document.getElementById('section_name').value = data.section_name;
      document.getElementById('year_level').value = data.year_level;
      document.getElementById('semester').value = data.semester;
      document.getElementById('course').value = data.course;
      document.getElementById('academic_year').value = data.academic_year;
      document.getElementById('schedule_day').value = data.schedule_day;
      document.getElementById('schedule_time_start').value = data.schedule_time_start;
      document.getElementById('schedule_time_end').value = data.schedule_time_end;
      document.getElementById('room').value = data.room;
      document.getElementById('instructor').value = data.instructor;
      document.getElementById('subject_id_select').value = data.subject_id;
    }
    
    function deleteSection(id) {
      if (confirm('Are you sure you want to delete this section?')) {
        window.location.href = 'admin_dashboard.php?delete_section=' + id;
      }
    }
    
    function deleteStudent(id) {
      if (confirm('Are you sure you want to delete this student?')) {
        window.location.href = 'admin_dashboard.php?delete_student=' + id;
      }
    }
    
    function closeSubjectModal() {
      document.getElementById('subjectModal').style.display = 'none';
    }
    
    function closeCurriculumModal() {
      document.getElementById('curriculumModal').style.display = 'none';
    }
    
    function closeSectionModal() {
      document.getElementById('sectionModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    }
  </script>
</body>
</html>

<?php $conn->close(); ?>