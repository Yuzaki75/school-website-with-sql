<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

$assignments = [];

if ($role === 'student') {
    // Students see assignments for subjects they're enrolled in
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.description, a.due_date, a.created_at, s.subject_name, u.full_name as teacher_name,
               COALESCE(asub.grade, 'Not graded') as grade, asub.submitted_at, asub.id as submission_id
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        JOIN users u ON a.assigned_by = u.id
        JOIN student_subjects ss ON s.id = ss.subject_id
        LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id AND asub.student_id = ?
        WHERE ss.student_id = ?
        ORDER BY a.due_date ASC
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
} elseif ($role === 'teacher') {
    // Teachers see assignments they created
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.description, a.due_date, a.created_at, s.subject_name,
               COUNT(asub.id) as submission_count
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id
        WHERE a.assigned_by = ?
        GROUP BY a.id
        ORDER BY a.due_date ASC
    ");
    $stmt->bind_param("i", $user_id);
} else {
    // Admin sees all assignments
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.description, a.due_date, a.created_at, s.subject_name, u.full_name as teacher_name,
               COUNT(asub.id) as submission_count
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        JOIN users u ON a.assigned_by = u.id
        LEFT JOIN assignment_submissions asub ON a.id = asub.assignment_id
        GROUP BY a.id
        ORDER BY a.due_date ASC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
$assignments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
    <title>Assignments</title>
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
            max-width: 1200px;
            background: rgba(0,0,0,0.6);
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        .assignment-card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .assignment-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .assignment-meta {
            font-size: 14px;
            color: #ccc;
            margin-bottom: 10px;
        }
        .due-date {
            color: #ff6b6b;
            font-weight: bold;
        }
        .submitted {
            color: #4BB543;
        }
        .btn {
            margin-right: 5px;
            margin-top: 5px;
        }
        .add-assignment-btn {
            margin-bottom: 20px;
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Assignments</h2>
        <?php
        $back_url = '';
        if ($role === 'student') {
            $back_url = '../dashboard_student.php';
        } elseif ($role === 'teacher') {
            $back_url = '../dashboard_teacher.php';
        } elseif ($role === 'admin') {
            $back_url = '../admin_dashboard.php';
        }
        if ($back_url): ?>
            <a href="<?= $back_url ?>" class="btn btn-outline-light">&larr; Back to Dashboard</a>
        <?php endif; ?>
    </div>

    <?php if ($role === 'teacher' || $role === 'admin'): ?>
        <a href="add_assignment.php" class="btn btn-primary add-assignment-btn">Add New Assignment</a>
    <?php endif; ?>

    <?php if (empty($assignments)): ?>
        <p>No assignments found.</p>
    <?php else: ?>
        <?php foreach ($assignments as $assignment): ?>
            <div class="assignment-card">
                <div class="assignment-title"><?= htmlspecialchars($assignment['title']) ?></div>
                <div class="assignment-meta">
                    Subject: <?= htmlspecialchars($assignment['subject_name']) ?> |
                    Due: <span class="due-date"><?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?></span>
                    <?php if ($role === 'student'): ?>
                        | Teacher: <?= htmlspecialchars($assignment['teacher_name']) ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($assignment['description'])): ?>
                    <p><?= htmlspecialchars($assignment['description']) ?></p>
                <?php endif; ?>

                <?php if ($role === 'student'): ?>
                    <?php if ($assignment['submission_id']): ?>
                        <div class="submitted">Submitted on: <?= date('M d, Y H:i', strtotime($assignment['submitted_at'])) ?> | Grade: <?= htmlspecialchars($assignment['grade']) ?></div>
                    <?php else: ?>
                        <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-success btn-sm">Submit Assignment</a>
                    <?php endif; ?>
                <?php elseif ($role === 'teacher' || $role === 'admin'): ?>
                    <div>Submissions: <?= $assignment['submission_count'] ?></div>
                    <a href="grade_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-primary btn-sm">Grade Submissions</a>
                    <a href="edit_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
