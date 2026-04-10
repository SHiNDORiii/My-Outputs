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
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = mysqli_real_escape_string($conn, $_POST['password']);
    
    if (empty($username) || empty($password_input)) {
        $message = "Please enter both username and password.";
        $message_type = "error";
    } else {
        $sql = "SELECT admin_id, username, password, email, full_name FROM admin WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            
            // Check password (for the default admin with plain text password '123')
            if ($password_input == $admin['password'] || password_verify($password_input, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_fullname'] = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_logged_in'] = true;
                
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $message = "Invalid username or password.";
                $message_type = "error";
            }
        } else {
            $message = "Admin account not found.";
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
  <title>Admin Login · HCC Schedule Portal</title>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    body {
      min-height: 100vh;
      background: linear-gradient(145deg, #e0f2fe 0%, #bae6fd 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(12px);
      border-radius: 2.5rem;
      box-shadow: 0 30px 50px rgba(0, 35, 70, 0.2);
      width: 100%;
      max-width: 450px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .brand-panel {
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      padding: 2rem;
      text-align: center;
      color: white;
    }
    
    .brand-panel h2 {
      font-size: 1.5rem;
      margin-top: 0.5rem;
    }
    
    .brand-panel p {
      opacity: 0.8;
      margin-top: 0.5rem;
    }
    
    .admin-icon {
      font-size: 4rem;
      background: rgba(255,255,255,0.2);
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
    }
    
    .form-panel {
      padding: 2rem;
      background: white;
    }
    
    .input-group {
      margin-bottom: 1.5rem;
    }
    
    .input-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #1e3c5a;
    }
    
    .input-icon {
      position: relative;
    }
    
    .input-icon i {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #8699ad;
    }
    
    .input-icon input {
      width: 100%;
      padding: 1rem 1rem 1rem 3rem;
      border: 1.5px solid #e2eaf2;
      border-radius: 60px;
      font-size: 1rem;
      outline: none;
    }
    
    .input-icon input:focus {
      border-color: #2a5298;
    }
    
    .login-btn {
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: white;
      border: none;
      padding: 1rem;
      border-radius: 60px;
      font-size: 1rem;
      font-weight: 600;
      width: 100%;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .login-btn:hover {
      transform: scale(1.02);
    }
    
    .message {
      padding: 1rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: 500;
    }
    
    .message.error {
      background: #f8d7da;
      border: 2px solid #dc3545;
      color: #721c24;
    }
    
    .back-link {
      text-align: center;
      margin-top: 1rem;
    }
    
    .back-link a {
      color: #2a5298;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="brand-panel">
      <div class="admin-icon">
        <i class="fas fa-user-shield"></i>
      </div>
      <h2>Admin Portal</h2>
      <p>Holy Cross College Pampanga</p>
    </div>
    
    <div class="form-panel">
      <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
          <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="">
        <div class="input-group">
          <label for="username">Username</label>
          <div class="input-icon">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" placeholder="Enter admin username" required>
          </div>
        </div>
        
        <div class="input-group">
          <label for="password">Password</label>
          <div class="input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="··········" required>
          </div>
        </div>
        
        <button type="submit" class="login-btn">
          <i class="fas fa-sign-in-alt"></i> Login as Admin
        </button>
      </form>
      
      <div class="back-link">
        <a href="index.php">← Back to Student Portal</a>
      </div>
    </div>
  </div>
</body>
</html>