<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in and is admin or teacher
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $subject_id = $_POST['subject_id'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $grade_type = $_POST['grade_type'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $academic_year = $_POST['academic_year'] ?? '';

    if (empty($student_id) || empty($subject_id) || $grade === '' || empty($grade_type) || empty($semester) || empty($academic_year)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO grades (student_id, subject_id, grade, grade_type, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsss", $student_id, $subject_id, $grade, $grade_type, $semester, $academic_year);
        if ($stmt->execute()) {
            $success = "Grade added successfully.";
        } else {
            $error = "Failed to add grade.";
        }
        $stmt->close();
    }
}

// Fetch students
$students_result = $conn->query("SELECT id, full_name FROM users WHERE role = 'student' ORDER BY full_name ASC");
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// Fetch courses
$courses_result = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch subjects
$subjects_result = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name ASC");
$subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
}

$username = $_SESSION['username'];

// fetch profile picture fresh from DB
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

if (empty($profile_pic_db)) {
    $profilePic = '../uploads/profile/default.png';
} else {
    $profilePic = '../uploads/profile/' . basename($profile_pic_db);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Grade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="../css/dashboard.css" />
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
        label {
            font-weight: bold;
        }
        select, input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: none;
        }
        button {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #ff6b6b;
            margin-bottom: 15px;
        }
        .success {
            color: #4BB543;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<header class="dashboard-header">
    <div class="header-left">
        <img src="../uploads/logo.png" alt="School Logo" class="school-logo" />
        <span class="school-name">Dominican College of Santa Rosa</span>
    </div>
    <div class="header-right">
        <span class="username">Hello, <?= htmlspecialchars($username) ?></span>
        <a href="../profile/view_profile.php">
            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="profile-avatar" />
        </a>
        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</header>

<div class="container">
    <h2>Add Grade</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
        <label for="student_id">Student:</label>
        <select id="student_id" name="student_id" required>
            <option value="">Select Student</option>
            <?php foreach ($students as $student): ?>
                <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['full_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="grade_type">Grade Type:</label>
        <select id="grade_type" name="grade_type" required>
            <option value="">Select Grade Type</option>
            <option value="quiz">Quiz</option>
            <option value="exam">Exam</option>
            <option value="assignment">Assignment</option>
            <option value="project">Project</option>
            <option value="final">Final</option>
        </select>

        <label for="semester">Semester:</label>
        <select id="semester" name="semester" required>
            <option value="">Select Semester</option>
            <option value="1st">1st</option>
            <option value="2nd">2nd</option>
            <option value="summer">Summer</option>
        </select>

        <label for="academic_year">Academic Year:</label>
        <input type="text" id="academic_year" name="academic_year" placeholder="e.g. 2023-2024" required />

        <label for="course_id">Course:</label>
        <select id="course_id" name="course_id" required>
            <option value="">Select Course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="subject_id">Subject:</label>
        <select id="subject_id" name="subject_id" required>
            <option value="">Select Subject</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="grade">Grade:</label>
        <input type="number" id="grade" name="grade" min="0" max="100" step="0.01" required />

        <button type="submit">Add Grade</button>
    </form>
</div>
</body>
</html>
