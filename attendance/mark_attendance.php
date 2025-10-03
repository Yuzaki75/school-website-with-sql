<?php
session_start();
require_once '../config/db.php';

// Protect the page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    $errors = [];

    if (!$student_id) {
        $errors[] = "Invalid student selected.";
    }
    if (!$course_id) {
        $errors[] = "Invalid course selected.";
    }
    if (!$date) {
        $errors[] = "Invalid date.";
    }
    $valid_statuses = ['Present', 'Absent', 'Late'];
    if (!in_array($status, $valid_statuses)) {
        $errors[] = "Invalid status selected.";
    }

    if (empty($errors)) {
        // Check if attendance already exists
        $stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND course_id = ? AND date = ?");
        $stmt->bind_param("iis", $student_id, $course_id, $date);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $stmt = $conn->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND course_id = ? AND date = ?");
            $stmt->bind_param("siis", $status, $student_id, $course_id, $date);
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, course_id, date, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $student_id, $course_id, $date, $status);
        }
        
        if ($stmt->execute()) {
            $success_message = "Attendance marked successfully!";
        } else {
            $error_message = "Error marking attendance.";
        }
        $stmt->close();
    }
}

// Fetch students list
$students = [];
$result = $conn->query("SELECT id, full_name FROM users WHERE role = 'student' ORDER BY full_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch courses list
$courses = [];
$result = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/attendance.css">
    <</head>
<body>
<header class="dashboard-header">
    <div class="header-left">
        <img src="../uploads/logo.png" alt="School Logo" class="school-logo">
        <span class="school-name">Dominican College of Santa Rosa</span>
    </div>
    <div class="header-right">
        <span class="username">Hello, <?= htmlspecialchars($username) ?></span>
        <a href="../profile/view_profile.php">
            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="profile-avatar">
        </a>
        <a href="attendance.php" class="btn btn-outline-light">Back</a>
    </div>
</header>

<div class="container">
    <h2 class="text-center mb-4">Mark Attendance</h2>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $error): ?>
                <p class="mb-0"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="student_id">Student:</label>
            <select name="student_id" id="student_id" class="form-select" required>
                <option value="">Select Student</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student['id']) ?>">
                        <?= htmlspecialchars($student['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="course_id">Course:</label>
            <select name="course_id" id="course_id" class="form-select" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['id']) ?>">
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" name="date" id="date" class="form-control" required 
                   value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label for="status">Status:</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
                <option value="Late">Late</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Mark Attendance</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
