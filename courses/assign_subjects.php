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

// Get course id from query param
$course_id = $_GET['id'] ?? 0;
$course_id = (int)$course_id;

// Fetch course info
$stmt = $conn->prepare("SELECT id, course_name FROM courses WHERE id=?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Fetch all subjects
$subjects_result = $conn->query("SELECT * FROM subjects ORDER BY subject_name ASC");
$all_subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $all_subjects[] = $row;
}

// Fetch subjects assigned to this course
$course_subjects_result = $conn->prepare("SELECT subject_id FROM course_subjects WHERE course_id = ?");
$course_subjects_result->bind_param("i", $course_id);
$course_subjects_result->execute();
$course_subjects_result->bind_result($subject_id);
$assigned_subjects = [];
while ($course_subjects_result->fetch()) {
    $assigned_subjects[] = $subject_id;
}
$course_subjects_result->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_subjects = $_POST['subjects'] ?? [];

    // Delete old assignments
    $stmt_del = $conn->prepare("DELETE FROM course_subjects WHERE course_id=?");
    $stmt_del->bind_param("i", $course_id);
    $stmt_del->execute();

    // Insert new assignments
    if (!empty($selected_subjects)) {
        $stmt_ins = $conn->prepare("INSERT INTO course_subjects (course_id, subject_id) VALUES (?, ?)");
        foreach ($selected_subjects as $subject_id) {
            $subject_id = (int)$subject_id;
            $stmt_ins->bind_param("ii", $course_id, $subject_id);
            $stmt_ins->execute();
        }
        $stmt_ins->close();
    }

    $success = "Subjects updated successfully.";
    // Refresh assigned subjects
    $assigned_subjects = $selected_subjects;
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
    <title>Assign Subjects to Course</title>
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
        <a href="courses.php" class="back-link">Back</a>
    </div>
</header>

<div class="container">
    <h2>Assign Subjects to Course: <?= htmlspecialchars($course['course_name']) ?></h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
        <label for="subjects">Select Subjects:</label>
        <select id="subjects" name="subjects[]" multiple>
            <?php foreach ($all_subjects as $subject): ?>
                <option value="<?= $subject['id'] ?>" <?= in_array($subject['id'], $assigned_subjects) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Update Subjects</button>
    </form>
</div>
</body>
</html>
