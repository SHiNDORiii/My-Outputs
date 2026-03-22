<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $birth_date = !empty($_POST['birth_date']) ? mysqli_real_escape_string($conn, $_POST['birth_date']) : NULL;
    $contact_number = !empty($_POST['contact_number']) ? mysqli_real_escape_string($conn, $_POST['contact_number']) : NULL;
    $address = !empty($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : NULL;
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    
    // Validation
    if (empty($email) || empty($first_name) || empty($last_name) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if email already exists
        $check_email = "SELECT student_id FROM students WHERE email = '$email'";
        $result = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email already registered. Please use a different email.';
        } else {
            // Generate student number
            $year = date('Y');
            $query = "SELECT COUNT(*) as count FROM students WHERE student_number LIKE 'HCCP-$year-%'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $count = $row['count'] + 1;
            $student_number = "HCCP-$year-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into database - students can log in immediately (Enrolled status)
            $sql = "INSERT INTO students (student_number, email, password, first_name, last_name, gender, birth_date, contact_number, address, year_level, enrollment_status, is_active, created_at) 
                    VALUES ('$student_number', '$email', '$hashed_password', '$first_name', '$last_name', '$gender', " . ($birth_date ? "'$birth_date'" : "NULL") . ", " . ($contact_number ? "'$contact_number'" : "NULL") . ", " . ($address ? "'$address'" : "NULL") . ", '$year_level', 'Enrolled', 1, NOW())";
            
            if (mysqli_query($conn, $sql)) {
                $success = 'Account created successfully! Your student number is: ' . $student_number . '. You can now log in.';
                // Clear form
                $_POST = array();
            } else {
                $error = 'Registration failed: ' . mysqli_error($conn);
            }
        }
    }
}
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
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
                <p>Join Holy Cross College Pampanga Schedule Portal</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="signup.php">
                <div class="input-group">
                    <label for="email">Email <span style="color: #dc3545;">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="e.g. student@hccp.edu.ph" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="first_name">First Name <span style="color: #dc3545;">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter First Name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="last_name">Last Name <span style="color: #dc3545;">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter Last Name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="gender">Gender <span style="color: #dc3545;">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-venus-mars"></i>
                        <select id="gender" name="gender" style="width: 100%; padding: 1rem 1rem 1rem 3rem; border: 1.5px solid #e2eaf2; border-radius: 60px; font-size: 1rem; background: #f9fcff;" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label for="birth_date">Birth Date</label>
                    <div class="input-icon">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="date" id="birth_date" name="birth_date" value="<?php echo isset($_POST['birth_date']) ? htmlspecialchars($_POST['birth_date']) : ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label for="contact_number">Contact Number</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" id="contact_number" name="contact_number" placeholder="e.g. 09123456789" value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fas fa-home"></i>
                        <input type="text" id="address" name="address" placeholder="Enter your address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label for="year_level">Year Level <span style="color: #dc3545;">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-graduation-cap"></i>
                        <select id="year_level" name="year_level" style="width: 100%; padding: 1rem 1rem 1rem 3rem; border: 1.5px solid #e2eaf2; border-radius: 60px; font-size: 1rem; background: #f9fcff;" required>
                            <option value="">Select Year Level</option>
                            <option value="Grade 7" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == 'Grade 7') ? 'selected' : ''; ?>>Grade 7</option>
                            <option value="Grade 8" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == 'Grade 8') ? 'selected' : ''; ?>>Grade 8</option>
                            <option value="Grade 9" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == 'Grade 9') ? 'selected' : ''; ?>>Grade 9</option>
                            <option value="Grade 10" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == 'Grade 10') ? 'selected' : ''; ?>>Grade 10</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Password <span style="color: #dc3545;">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password <span style="color: #dc3545;">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <span>Create Account</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <p class="signup-hint">
                <i class="fas fa-id-card"></i> Already have an account? <a href="index.php">Log In</a>
            </p>
        </div>
    </div>
</body>
</html>