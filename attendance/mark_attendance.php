<?php
// attendance/mark_attendance.php
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
    $subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $errors = [];

    if (!$student_id) {
        $errors[] = "Invalid student selected.";
    }
    if (!$subject_id) {
        $errors[] = "Invalid subject selected.";
    }
    if (!$date) {
        $errors[] = "Invalid date.";
    }
    $valid_statuses = ['present', 'absent', 'late', 'excused'];
    if (!in_array($status, $valid_statuses)) {
        $errors[] = "Invalid status selected.";
    }

    if (empty($errors)) {
        // Check if attendance already exists
        $stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND subject_id = ? AND date = ?");
        $stmt->bind_param("iis", $student_id, $subject_id, $date);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            $stmt = $conn->prepare("UPDATE attendance SET status = ?, marked_by = ? WHERE student_id = ? AND subject_id = ? AND date = ?");
            $stmt->bind_param("siiis", $status, $user_id, $student_id, $subject_id, $date);
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iissi", $student_id, $subject_id, $date, $status, $user_id);
        }

        if ($stmt->execute()) {
            $success_message = "Attendance marked successfully!";
        } else {
            $error_message = "Error marking attendance.";
        }
        $stmt->close();
    }
}

// Fetch students list using prepared statement
$students = [];
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE role = 'student' ORDER BY full_name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Fetch subjects list using prepared statement
$subjects = [];
$stmt = $conn->prepare("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
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
            max-width: 600px;
            background: rgba(0,0,0,0.6);
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        h2 {
            color: #fff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            color: #fff;
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
            <label for="subject_id">Subject:</label>
            <select name="subject_id" id="subject_id" class="form-select" required>
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= htmlspecialchars($subject['id']) ?>">
                        <?= htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']) ?>
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
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="late">Late</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Mark Attendance</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
