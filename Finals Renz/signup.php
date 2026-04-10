<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hcc_schedule";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$message_type = "";

// Function to generate next student number
function generateStudentNumber($conn) {
    $sql = "SELECT student_number FROM student WHERE student_number LIKE '2026-%' ORDER BY student_number DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_number = $row['student_number'];
        $last_id = intval(substr($last_number, 5));
        $new_id = $last_id + 1;
        return "2026-" . str_pad($new_id, 4, "0", STR_PAD_LEFT);
    } else {
        return "2026-0001";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = mysqli_real_escape_string($conn, $_POST['student_number']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = mysqli_real_escape_string($conn, $_POST['password']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    
    $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
    
    $valid_year_levels = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];
    $valid_courses = ['BSIT', 'BSCS', 'BSCpE', 'BSCE'];
    
    if (!in_array($year_level, $valid_year_levels)) {
        $message = "Invalid year level. Please select 1st Year, 2nd Year, 3rd Year, 4th Year, or 5th Year.";
        $message_type = "error";
    } elseif (!in_array($course, $valid_courses)) {
        $message = "Invalid course. Please select a valid course.";
        $message_type = "error";
    } else {
        $check_sql = "SELECT student_id FROM student WHERE student_number = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $student_number, $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $message = "Student number or email already exists!";
            $message_type = "error";
        } else {
            $insert_sql = "INSERT INTO student (student_number, first_name, last_name, email, password, contact_number, year_level, course) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssssssss", $student_number, $first_name, $last_name, $email, $hashed_password, $contact_number, $year_level, $course);
            
            if ($insert_stmt->execute()) {
                $message = "Account created successfully!";
                $message_type = "success";
                // Clear form fields
                $first_name = $last_name = $email = $password_input = $contact_number = $year_level = $course = "";
                // Generate new student number for next registration
                $student_number = generateStudentNumber($conn);
            } else {
                $message = "Error: " . $insert_stmt->error;
                $message_type = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
} else {
    // Generate initial student number on page load
    $student_number = generateStudentNumber($conn);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Holy Cross College Pampanga · Sign Up</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .input-with-button {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    
    .input-with-button input {
      flex: 1;
    }
    
    .generate-btn {
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: white;
      border: none;
      padding: 1rem 1.5rem;
      border-radius: 60px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      white-space: nowrap;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .generate-btn:hover {
      background: linear-gradient(135deg, #163257, #1f4172);
      transform: scale(1.02);
    }
    
    .form-scroll {
      overflow-y: auto;
      max-height: 500px;
      padding-right: 10px;
      scrollbar-width: thin;
    }
    
    .form-scroll::-webkit-scrollbar {
      width: 6px;
    }
    
    .form-scroll::-webkit-scrollbar-track {
      background: #e2eaf2;
      border-radius: 10px;
    }
    
    .form-scroll::-webkit-scrollbar-thumb {
      background: #2a5298;
      border-radius: 10px;
    }
    
    .form-scroll::-webkit-scrollbar-thumb:hover {
      background: #1e3c72;
    }
    
    .message {
      padding: 1rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: 500;
      animation: slideDown 0.3s ease;
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
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .input-icon select {
      width: 100%;
      padding: 1rem 1rem 1rem 3rem;
      border: 1.5px solid #e2eaf2;
      border-radius: 60px;
      font-size: 1rem;
      background: #f9fcff;
      transition: all 0.2s;
      outline: none;
      color: #0a2a44;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      cursor: pointer;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .input-icon select:focus {
      border-color: #2a5298;
      background: white;
      box-shadow: 0 6px 16px rgba(0, 60, 130, 0.08);
    }
    
    .input-icon {
      position: relative;
    }
    
    .input-icon i.fa-chevron-down {
      position: absolute;
      right: 20px;
      left: auto;
      top: 50%;
      transform: translateY(-50%);
      color: #8699ad;
      font-size: 0.9rem;
      pointer-events: none;
      z-index: 2;
    }
    
    .input-icon select:focus + i {
      color: #1e3c72;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="brand-panel">
      <div class="brand-header">
        <div class="college-logo">
          <i class="fas fa-cross"></i>
        </div>
        <h2>Holy Cross College<br><span>Pampanga</span></h2>
      </div>
      
      <div class="logo-container">
        <img src="logohcc.png" alt="Holy Cross College Pampanga Logo" class="school-logo">
      </div>

      <div class="college-motto">
        <i class="fas fa-quote-left"></i> Fides, Caritas, Libertas <i class="fas fa-quote-right"></i>
      </div>
    </div>

    <div class="form-panel">
      <div class="form-header">
        <h1>Create <span>Account</span></h1>
        <p>Fill in your details to sign up</p>
      </div>

      <div class="form-scroll">
        <?php if ($message): ?>
          <div class="message <?php echo $message_type; ?>">
            <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
          </div>
        <?php endif; ?>
        
        <form method="POST" action="">
          <div class="input-group">
            <label for="student_number">Student Number</label>
            <div class="input-with-button">
              <div class="input-icon" style="flex: 1;">
                <i class="fas fa-id-card"></i>
                <input type="text" id="student_number" name="student_number" value="<?php echo isset($student_number) ? htmlspecialchars($student_number) : ''; ?>" required readonly style="background: #f0f2f5;">
              </div>
              <button type="button" class="generate-btn" onclick="generateStudentNumber()">
                <i class="fas fa-sync-alt"></i> Generate
              </button>
            </div>
          </div>

          <div class="input-group">
            <label for="first_name">First Name</label>
            <div class="input-icon">
              <i class="fas fa-user"></i>
              <input type="text" id="first_name" name="first_name" placeholder="Enter First Name" value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>" required>
            </div>
          </div>

          <div class="input-group">
            <label for="last_name">Last Name</label>
            <div class="input-icon">
              <i class="fas fa-user"></i>
              <input type="text" id="last_name" name="last_name" placeholder="Enter Last Name" value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>" required>
            </div>
          </div>

          <div class="input-group">
            <label for="email">Email</label>
            <div class="input-icon">
              <i class="fas fa-envelope"></i>
              <input type="email" id="email" name="email" placeholder="e.g. student@hccp.edu.ph" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
          </div>

          <div class="input-group">
            <label for="password">Password</label>
            <div class="input-icon">
              <i class="fas fa-lock"></i>
              <input type="password" id="password" name="password" placeholder="··········" required>
            </div>
          </div>

          <div class="input-group">
            <label for="contact_number">Contact Number</label>
            <div class="input-icon">
              <i class="fas fa-phone"></i>
              <input type="text" id="contact_number" name="contact_number" placeholder="Enter Contact Number" value="<?php echo isset($contact_number) ? htmlspecialchars($contact_number) : ''; ?>" required>
            </div>
          </div>

          <div class="input-group">
            <label for="year_level">Year Level</label>
            <div class="input-icon">
              <i class="fas fa-graduation-cap"></i>
              <select id="year_level" name="year_level" required>
                <option value="" disabled <?php echo empty($year_level) ? 'selected' : ''; ?>>Select Year Level</option>
                <option value="1st Year" <?php echo (isset($year_level) && $year_level == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                <option value="2nd Year" <?php echo (isset($year_level) && $year_level == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                <option value="3rd Year" <?php echo (isset($year_level) && $year_level == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                <option value="4th Year" <?php echo (isset($year_level) && $year_level == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                <option value="5th Year" <?php echo (isset($year_level) && $year_level == '5th Year') ? 'selected' : ''; ?>>5th Year</option>
              </select>
              <i class="fas fa-chevron-down"></i>
            </div>
          </div>

          <div class="input-group">
            <label for="course">Course</label>
            <div class="input-icon">
              <i class="fas fa-book"></i>
              <select id="course" name="course" required>
                <option value="" disabled <?php echo empty($course) ? 'selected' : ''; ?>>Select Course</option>
                <option value="BSIT" <?php echo (isset($course) && $course == 'BSIT') ? 'selected' : ''; ?>>BSIT - Bachelor of Science in Information Technology</option>
                <option value="BSCS" <?php echo (isset($course) && $course == 'BSCS') ? 'selected' : ''; ?>>BSCS - Bachelor of Science in Computer Science</option>
                <option value="BSCpE" <?php echo (isset($course) && $course == 'BSCpE') ? 'selected' : ''; ?>>BSCpE - Bachelor of Science in Computer Engineering</option>
                <option value="BSCE" <?php echo (isset($course) && $course == 'BSCE') ? 'selected' : ''; ?>>BSCE - Bachelor of Science in Civil Engineering</option>
              </select>
              <i class="fas fa-chevron-down"></i>
            </div>
          </div>

          <button type="submit" class="login-btn">
            <span>Sign Up</span>
            <i class="fas fa-arrow-right"></i>
          </button>
        </form>

        <p class="signup-hint">
          <i class="fas fa-id-card"></i> Already have an account? <a href="index.php">Log In</a>
        </p>
      </div>
    </div>
  </div>

  <script>
    function generateStudentNumber() {
      fetch('generate_student_number.php')
        .then(response => response.json())
        .then(data => {
          if (data.student_number) {
            document.getElementById('student_number').value = data.student_number;
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }
  </script>
</body>
</html>