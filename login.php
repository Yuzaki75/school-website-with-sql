<?php
session_start();
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username !== '' && $password !== '') {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'teacher':
                    header("Location: dashboard_teacher.php");
                    break;
                case 'student':
                    header("Location: dashboard_student.php");
                    break;
                default:
                    $error = "Unknown role";
                    break;
            }
            exit;
        } else {
            $error = "Invalid username or password";
        }

        $stmt->close();
    } else {
        $error = "Please enter both username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Student Portal</title>
    <link rel="stylesheet" href="css/login.css">
        <style>
        /* Entire page background */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('uploads/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        /* Blur overlay */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            background-color: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
    </style>
</head>
<body>
    <!-- Background layers -->
    <div class="bg-image"></div>
    <div class="bg-blur"></div>

    <!-- Header -->
    <header class="landing-header">
        <div class="header-left">
            <img src="uploads/logo.png" alt="School Logo" class="school-logo">
            <span class="school-name">Dominican College of Sta. Rosa, Laguna, Inc.</span>
        </div>
        <div class="header-right">
            <a href="index.php" class="login-btn">Home</a>
            <a href="about_us.php" class="login-btn">About Us</a>
            <a href="admissions.php" class="login-btn">Admissions</a>
        </div>
    </header>

    <!-- Login form -->
    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>
            <?php if ($error !== ''): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="post" action="login.php">
                <label>Username:</label><br>
                <input type="text" name="username" required><br><br>

                <label>Password:</label><br>
                <input type="password" name="password" required><br><br>

                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
