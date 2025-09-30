<?php
session_start();
require_once 'config/db.php'; // adjust path to your db.php

// Make sure user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// fetch profile picture fresh from DB
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

if (empty($profile_pic_db)) {
    // use default avatar if none set
    $profilePic = 'uploads/profile/default.png';
} else {
    $profilePic = 'uploads/profile/' . basename($profile_pic_db);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
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

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: rgba(0,0,0,0.8);
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .school-logo {
            height: 50px;
            margin-right: 10px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin: 30px;
        }

        .card {
            background: rgba(0,0,0,0.5);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .card a {
            text-decoration: none;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-left">
            <img src="uploads/logo.png" alt="School Logo" class="school-logo">
            <span class="school-name">Dominican College of Santa Rosa</span>
        </div>
        <div class="header-right">
            <span class="username">Hello, <?php echo htmlspecialchars($username); ?></span>
            <a href="profile/view_profile.php">
                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" class="profile-avatar">
            </a>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </header>

    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?> (Admin)</h2>
        <p>This is your admin dashboard.</p>

            <div class="cards">
                <div class="card"><a href="users/manage_users.php">Manage Users</a></div>
                <div class="card"><a href="courses/courses.php">Manage Courses</a></div>
                <div class="card"><a href="subjects/subjects.php">Manage Subjects</a></div>
                <div class="card"><a href="grades/grades.php">Manage Grades</a></div>
                <div class="card"><a href="attendance/attendance.php">Manage Attendance</a></div>
                <div class="card"><a href="announcements/announcements.php">Announcements</a></div>
                <div class="card"><a href="library/library.php">Library</a></div>
                <div class="card"><a href="messages/inbox.php">Messages</a></div>
                <div class="card"><a href="assignments/assignments.php">Assignments</a></div>
            </div>
    </div>
</body>
</html>
