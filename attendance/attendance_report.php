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
    <link rel="stylesheet" href="../css/attendance.css">
</head>
<body>
    <?php include '../includes/dashboard_header.php'; ?>
    <div class="container">
        <h2>Attendance Report</h2>

        <?php
        // Fetch attendance records grouped by student
        $sql = "SELECT u.full_name, a.date, a.status
                FROM attendance a
                JOIN users u ON a.student_id = u.id
                ORDER BY u.full_name, a.date DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            echo "<table class='attendance-table'><thead><tr><th>Student</th><th>Date</th><th>Status</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='no-records'>No attendance records found.</p>";
        }
        ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
