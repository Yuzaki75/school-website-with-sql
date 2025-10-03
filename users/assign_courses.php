<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// Get student id from query param
$student_id = $_GET['id'] ?? 0;
$student_id = (int)$student_id;

// Fetch student info
$stmt = $conn->prepare("SELECT id, username FROM users WHERE id=? AND role='student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: manage_users.php");
    exit();
}

// Fetch all courses
$courses_result = $conn->query("SELECT * FROM courses ORDER BY course_name ASC");
$all_courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $all_courses[] = $row;
}

// Fetch courses assigned to this student
$student_courses_result = $conn->prepare("SELECT course_id FROM enrollments WHERE student_id = ?");
$student_courses_result->bind_param("i", $student_id);
$student_courses_result->execute();
$student_courses_result->bind_result($course_id);
$assigned_courses = [];
while ($student_courses_result->fetch()) {
    $assigned_courses[] = $course_id;
}
$student_courses_result->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_courses = $_POST['courses'] ?? [];

    // Delete old enrollments
    $stmt_del = $conn->prepare("DELETE FROM enrollments WHERE student_id=?");
    $stmt_del->bind_param("i", $student_id);
    $stmt_del->execute();

    // Insert new enrollments
    if (!empty($selected_courses)) {
        $stmt_ins = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        foreach ($selected_courses as $course_id) {
            $course_id = (int)$course_id;
            $stmt_ins->bind_param("ii", $student_id, $course_id);
            $stmt_ins->execute();
        }
        $stmt_ins->close();
    }

    $success = "Courses updated successfully.";
    // Refresh assigned courses
    $assigned_courses = $selected_courses;
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
    $profilePic = '../uploads/profile/default.png';
} else {
    $profilePic = '../uploads/profile/' . basename($profile_pic_db);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Assign Courses to Student</title>
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
            max-width: 700px;
            background: rgba(0,0,0,0.6);
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        label {
            font-weight: bold;
        }
        select[multiple] {
            width: 100%;
            height: 200px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: none;
            padding: 8px;
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
    <h2>Assign Courses to Student: <?= htmlspecialchars($student['username']) ?></h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
        <label for="courses">Select Courses:</label>
        <select id="courses" name="courses[]" multiple>
            <?php foreach ($all_courses as $course): ?>
                <option value="<?= $course['id'] ?>" <?= in_array($course['id'], $assigned_courses) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['course_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Update Courses</button>
    </form>
</div>
</body>
</html>
