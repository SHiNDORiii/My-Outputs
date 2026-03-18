<?php
session_start();

include("connection.php");

$show_message = false;
$message_type = '';
$message_text = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $show_message = true;
        $message_type = 'error';
        $message_text = 'Invalid email format!';
    }
    else if (strlen($password) < 6) {
        $show_message = true;
        $message_type = 'error';
        $message_text = 'Password must be at least 6 characters!';
    }
    else {
        $check_email_query = "SELECT email FROM users WHERE email = '$email'";
        $check_email_result = mysqli_query($con, $check_email_query);
        
        if (mysqli_num_rows($check_email_result) > 0) {
            $show_message = true;
            $message_type = 'error';
            $message_text = 'Email already exists! Please use a different email.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_query = "INSERT INTO users (first_name, last_name, email, password) 
                            VALUES ('$first_name', '$last_name', '$email', '$hashed_password')";
            
            if (mysqli_query($con, $insert_query)) {
                $show_message = true;
                $message_type = 'success';
                $message_text = 'Registration successful! You can now log in.';
            } else {
                $show_message = true;
                $message_type = 'error';
                $message_text = 'Registration failed: ' . mysqli_error($con);
            }
        }
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Register | HCC Schedule Checker</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <?php if ($show_message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message_text; ?>
                <?php if ($message_type == 'success'): ?>
                    <br><a href="index.php">Click here to log in</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <h1>Register</h1>
            
            <div class="input-box">
                <input type="text" name="first_name" placeholder="Enter First Name" 
                       value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                <i class='bx bxs-user'></i>
            </div>
            
            <div class="input-box">
                <input type="text" name="last_name" placeholder="Enter Last Name" 
                       value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                <i class='bx bxs-user'></i>
            </div>
            
            <div class="input-box">
                <input type="email" name="email" placeholder="Enter Email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <i class="bx bx-envelope"></i>
            </div>
            
            <div class="input-box">
                <input type="password" name="password" placeholder="Enter Password (min. 6 characters)" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            
            <button type="submit" class="btn">Sign Up</button>
            
            <div class="register-link">
                <p>Already have an account? <a href="index.php">Log In</a></p>
            </div>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>