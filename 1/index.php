<?php
session_start();
include("connection.php");

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];

                header("Location: home.php");
                exit();
            } else {
                $error_message = "Invalid email or password. Please try again.";
            }
        } else {
            $error_message = "Invalid email or password. Please try again.";
        }
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Login | HCC Schedule Checker</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .error-message {
            color: #ff6b6b;
            font-size: 14px;
            margin: 5px 0 15px 10px;
            text-align: left;
            background: rgba(255, 107, 107, 0.1);
            padding: 8px 15px;
            border-radius: 40px;
            border: 1px solid #ff6b6b;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <form method="POST" action="">
            <h1>Login</h1>
            
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <i class='bx bxs-user'></i>
            </div>
            
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            
            <!-- ERROR MESSAGE HERE - BELOW PASSWORD BOX -->
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <br>
            
            <div class="remember-forgot">
                <label><input type="checkbox" name="remember"> Remember Me</label>
            </div>
            
            <button type="submit" class="btn" name="submit">Login</button>
            
            <div class="register-link">
                <p>New Student/Staff? <a href="register.php">Register</a></p>
            </div>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>