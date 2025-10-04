<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// Protect the page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Fetch profile picture
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

$profilePic = !empty($profile_pic_db) 
    ? '../uploads/profile/' . basename($profile_pic_db) 
    : '../uploads/profile/default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('../uploads/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            min-height: 100vh;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            backdrop-filter: blur(6px);
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
        .container {
            margin: 30px auto;
            max-width: 900px;
            background: rgba(0,0,0,0.6);
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        h2, h3 {
            color: #fff;
        }
        .table thead {
            background-color: #343a40;
            color: #fff;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .card {
            background: rgba(0,0,0,0.6) !important;
        }
    </style>
    </head>
<body>
<header class="dashboard-header">
    <div class="header-left">
        <img src="../uploads/logo.png" alt="School Logo" class="school-logo">
        <span class="school-name">Dominican College of Santa Rosa</span>
    </div>
    <div class="header-right">
        <span class="username" style="color: #FFA500;">Hello, <?= htmlspecialchars($username) ?></span>
        <div class="d-flex align-items-center gap-3">
            <a href="../profile/view_profile.php" class="profile-link">
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="profile-avatar">
            </a>
            <a href="../admin_dashboard.php" class="btn" style="color: #FFA500; border: 1px solid #FFA500;">Back</a>
        </div>
    </div>
</header>

<div class="container">
    <h2 class="mb-4 text-center">Attendance Management System</h2>
    
    <?php if ($role === 'student'): ?>
        <h3 class="mb-3">Your Attendance Records</h3>
        <?php
        $stmt = $conn->prepare("
            SELECT a.date, a.status, s.subject_name, c.course_name
            FROM attendance a
            JOIN subjects s ON a.subject_id = s.id
            JOIN courses c ON s.course_id = c.id
            WHERE a.student_id = ?
            ORDER BY a.date DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-dark table-striped attendance-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Course</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('F j, Y', strtotime($row['date']))) ?></td>
                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                <td class="<?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars(ucfirst($row['status'])) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No attendance records found.</div>
        <?php endif;
        $stmt->close();
        ?>
    <?php else: ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-dark text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Mark Attendance</h5>
                        <p class="card-text">Record student attendance for today's classes.</p>
                        <a href="mark_attendance.php" class="btn btn-primary">Mark Attendance</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-dark text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Attendance Reports</h5>
                        <p class="card-text">View and generate attendance reports.</p>
                        <a href="attendance_report.php" class="btn btn-primary">View Reports</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
