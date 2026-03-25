<?php
session_start();

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = mysqli_real_escape_string($conn, $_POST['password']);
    
    if (empty($email) || empty($password_input)) {
        $message = "Please enter both email and password.";
        $message_type = "error";
    } else {
        $sql = "SELECT student_id, student_number, first_name, last_name, email, password, course, year_level FROM student WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password_input, $user['password'])) {
                $_SESSION['user_id'] = $user['student_id'];
                $_SESSION['student_number'] = $user['student_number'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['course'] = $user['course'];
                $_SESSION['year_level'] = $user['year_level'];
                $_SESSION['logged_in'] = true;
                
                header("Location: homepage.php");
                exit();
            } else {
                $message = "Invalid email or password.";
                $message_type = "error";
            }
        } else {
            $message = "No account found with this email.";
            $message_type = "error";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Holy Cross College Pampanga · Schedule Portal</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
  <link rel="stylesheet" href="style.css">
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
        <h1>Welcome <span>back</span></h1>
        <p>Enter your credentials to check your schedule</p>
      </div>

      <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
          <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="input-group">
          <label for="email">Email</label>
          <div class="input-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" placeholder="e.g. student@hccp.edu.ph" required>
          </div>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <div class="input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="··········" required>
          </div>
        </div>

        <div class="form-extras">
          <a href="#" class="forgot-link">Forgot Password?</a>
        </div>

        <button type="submit" class="login-btn">
          <span>Log In</span>
          <i class="fas fa-arrow-right"></i>
        </button>
      </form>

      <p class="signup-hint">
        <i class="fas fa-id-card"></i> New to Holy Cross College? <a href="signup.php">Sign Up</a>
      </p>
    </div>
  </div>
</body>
</html>