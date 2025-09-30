<?php
http_response_code(404); // send 404 header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 – Page Not Found | Dominican College of Sta. Rosa, Laguna, Inc.</title>
    <link rel="icon" type="uploads/logo.png" href="uploads/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('uploads/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            background-color: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }

        .error-container {
            text-align: center;
            padding: 100px 20px;
            color: #fff;
        }
        .error-container h1 {
            font-size: 80px;
            margin: 0;
        }
        .error-container p {
            font-size: 20px;
            margin: 10px 0 30px;
        }
        .error-container a {
            display: inline-block;
            padding: 10px 20px;
            background: black;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .error-container a:hover {
            background: #004c99;
        }

        /* Optional: style for the close button at bottom of menu */
        .close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            margin-top: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

    <!-- 404 content -->
    <div class="error-container">
        <h1>404</h1>
        <p>Oops! The page you’re looking for can’t be found.</p>
        <a href="index.php">Return to Home</a>
    </div>

    <script>
        // Burger open
        document.getElementById('burgerBtn').addEventListener('click', function () {
            document.getElementById('sideMenu').classList.toggle('open');
        });
        // Close button at bottom of menu
        document.getElementById('closeMenu').addEventListener('click', function () {
            document.getElementById('sideMenu').classList.remove('open');
        });
    </script>
</body>
</html>
