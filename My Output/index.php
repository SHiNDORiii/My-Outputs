<?php
session_start();
require_once 'config.php';

$error = '';

if (isset($_SESSION['student_id'])) {
    header('Location: homepage.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? true : false;
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password';
    } else {
        $sql = "SELECT student_id, student_number, email, password, first_name, last_name, year_level, is_active FROM students WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) == 1) {
            $student = mysqli_fetch_assoc($result);
            
            if ($student['is_active'] == 0) {
                $error = 'Your account has been deactivated. Please contact the administrator.';
            } elseif (password_verify($password, $student['password'])) {
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['student_number'] = $student['student_number'];
                $_SESSION['email'] = $student['email'];
                $_SESSION['first_name'] = $student['first_name'];
                $_SESSION['last_name'] = $student['last_name'];
                $_SESSION['year_level'] = $student['year_level'];
                
                // Remove the last_login update since column doesn't exist
                // $update_sql = "UPDATE students SET last_login = NOW() WHERE student_id = " . $student['student_id'];
                // mysqli_query($conn, $update_sql);
                
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 7), '/');
                    $token_sql = "UPDATE students SET remember_token = '$token' WHERE student_id = " . $student['student_id'];
                    mysqli_query($conn, $token_sql);
                }
                
                header('Location: homepage.php');
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'No account found with this email address';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holy Cross College Pampanga · Schedule Portal</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link rel="stylesheet" href="style.css">
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
                <h1>Welcome <span>back</span></h1>
                <p>Enter your credentials to check your schedule</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php">
                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="e.g. student@hccp.edu.ph" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
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
                    <label class="remember-me">
                        <input type="checkbox" name="remember_me" <?php echo isset($_POST['remember_me']) ? 'checked' : ''; ?>> 
                        <span>Keep me signed in</span>
                    </label>
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