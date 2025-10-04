<?php
session_start();
require_once '../config/db.php';

// Protect the page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
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
            max-width: 1200px;
            background: rgba(0,0,0,0.6);
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        h2 {
            color: #fff;
        }
        .table thead {
            background-color: #343a40;
            color: #fff;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: rgba(0,0,0,0.8);
            color: #fff;
            text-align: center;
            padding: 10px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <?php include '../includes/dashboard_header.php'; ?>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Attendance Report</h2>
            <a href="attendance.php" class="btn btn-outline-light">Back</a>
        </div>

        <?php
        // Fetch attendance records with subject, course, and attendance details
        $sql = "SELECT u.full_name, s.subject_name, c.course_name, a.date, a.status
                FROM attendance a
                JOIN users u ON a.student_id = u.id
                JOIN subjects s ON a.subject_id = s.id
                LEFT JOIN courses c ON s.course_id = c.id
                ORDER BY u.full_name, a.date DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-dark table-striped'>";
            echo "<thead><tr><th>Student</th><th>Subject</th><th>Course</th><th>Date</th><th>Status</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                echo "<td>" . htmlspecialchars(date('F j, Y', strtotime($row['date']))) . "</td>";
                echo "<td>" . htmlspecialchars(ucfirst($row['status'])) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-info'>No attendance records found.</div>";
        }
        ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
