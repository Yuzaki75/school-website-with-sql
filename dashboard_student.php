<?php
session_start();
require_once 'config/db.php'; // adjust path to your db.php

// Protect the page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
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
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'includes/dashboard_header.php'; ?>

    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?> (Student)</h2>
        <p>Here you can access your classes, assignments, and announcements.</p>

        <div class="cards">
            <div class="card"><a href="courses/courses.php">Courses</a></div>
            <div class="card"><a href="subjects/subjects.php">Subjects</a></div>
            <div class="card"><a href="grades/grades.php">My Grades</a></div>
            <div class="card"><a href="assignments/assignments.php">Assignments</a></div>
            <div class="card"><a href="attendance/attendance_report.php">My Attendance</a></div>
        </div>

        <h3>Your Assigned Subjects</h3>
        <div class="assigned-subjects">
            <?php
            // Fetch assigned subjects for the student
            $stmt = $conn->prepare("SELECT s.subject_name FROM student_subjects ss JOIN subjects s ON ss.subject_id = s.id WHERE ss.student_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo '<ul>';
                while ($row = $result->fetch_assoc()) {
                    echo '<li>' . htmlspecialchars($row['subject_name']) . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No subjects assigned yet.</p>';
            }
            $stmt->close();
            ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
