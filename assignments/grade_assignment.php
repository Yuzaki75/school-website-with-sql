<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in and is teacher or admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$assignment_id = $_GET['id'] ?? '';

if (empty($assignment_id)) {
    header("Location: assignments.php");
    exit();
}

// Check if assignment exists and belongs to current teacher (or admin)
$role_check = $_SESSION['role'] === 'admin' ? "1=1" : "a.assigned_by = " . $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT a.id, a.title, a.description, a.due_date, s.subject_name
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.id = ? AND $role_check
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: assignments.php");
    exit();
}

$assignment = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])) {
    $submission_id = $_POST['submission_id'];
    $grade = trim($_POST['grade'] ?? '');
    $feedback = trim($_POST['feedback'] ?? '');

    if (!empty($grade) && (!is_numeric($grade) || $grade < 0 || $grade > 100)) {
        $error = "Grade must be a number between 0 and 100.";
    } else {
        $stmt = $conn->prepare("UPDATE assignment_submissions SET grade = ?, feedback = ?, graded_at = NOW() WHERE id = ? AND assignment_id = ?");
        $grade_value = empty($grade) ? null : $grade;
        $stmt->bind_param("ssii", $grade_value, $feedback, $submission_id, $assignment_id);
        if ($stmt->execute()) {
            $success = "Submission graded successfully.";
        } else {
            $error = "Failed to grade submission.";
        }
        $stmt->close();
    }
}

// Fetch all submissions for this assignment
$stmt = $conn->prepare("
    SELECT asub.id, asub.submission_file, asub.submission_text, asub.grade, asub.feedback,
           asub.submitted_at, asub.graded_at, u.full_name as student_name, u.username
    FROM assignment_submissions asub
    JOIN users u ON asub.student_id = u.id
    WHERE asub.assignment_id = ?
    ORDER BY asub.submitted_at DESC
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
    <title>Grade Assignment</title>
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
        .assignment-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .assignment-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .assignment-meta {
            font-size: 14px;
            color: #ccc;
        }
        .submission-card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .submission-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .student-name {
            font-weight: bold;
            font-size: 16px;
        }
        .submission-meta {
            font-size: 14px;
            color: #ccc;
        }
        .submission-content {
            background: rgba(255,255,255,0.05);
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            white-space: pre-wrap;
        }
        .grade-form {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input[type="number"], textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: none;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button {
            background-color: #007bff;
            border: none;
            padding: 8px 16px;
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
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .download-link {
            color: #007bff;
            text-decoration: none;
        }
        .download-link:hover {
            text-decoration: underline;
        }
        .graded {
            border-left: 4px solid #4BB543;
        }
        .grade-display {
            background: rgba(75, 181, 67, 0.2);
            border: 1px solid #4BB543;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
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
    <a href="assignments.php" class="back-link">&larr; Back to Assignments</a>
    <h2>Grade Assignment Submissions</h2>

    <div class="assignment-info">
        <div class="assignment-title"><?= htmlspecialchars($assignment['title']) ?></div>
        <div class="assignment-meta">
            Subject: <?= htmlspecialchars($assignment['subject_name']) ?> |
            Due: <?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?> |
            Total Submissions: <?= count($submissions) ?>
        </div>
        <?php if (!empty($assignment['description'])): ?>
            <p><strong>Description:</strong> <?= htmlspecialchars($assignment['description']) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (empty($submissions)): ?>
        <p>No submissions yet.</p>
    <?php else: ?>
        <?php foreach ($submissions as $submission): ?>
            <div class="submission-card <?= !empty($submission['grade']) ? 'graded' : '' ?>">
                <div class="submission-header">
                    <div class="student-name"><?= htmlspecialchars($submission['student_name']) ?> (<?= htmlspecialchars($submission['username']) ?>)</div>
                    <div class="submission-meta">Submitted: <?= date('M d, Y H:i', strtotime($submission['submitted_at'])) ?></div>
                </div>

                <?php if (!empty($submission['submission_text'])): ?>
                    <div><strong>Text Submission:</strong></div>
                    <div class="submission-content"><?= htmlspecialchars($submission['submission_text']) ?></div>
                <?php endif; ?>

                <?php if (!empty($submission['submission_file'])): ?>
                    <div><strong>File Submission:</strong> <a href="../<?= htmlspecialchars($submission['submission_file']) ?>" class="download-link" target="_blank">Download</a></div>
                <?php endif; ?>

                <?php if (!empty($submission['grade'])): ?>
                    <div class="grade-display">
                        <strong>Grade:</strong> <?= htmlspecialchars($submission['grade']) ?>/100
                        <?php if (!empty($submission['graded_at'])): ?>
                            (Graded on: <?= date('M d, Y H:i', strtotime($submission['graded_at'])) ?>)
                        <?php endif; ?>
                        <?php if (!empty($submission['feedback'])): ?>
                            <br><strong>Feedback:</strong> <?= nl2br(htmlspecialchars($submission['feedback'])) ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="grade-form">
                        <h5>Grade this submission:</h5>
                        <form method="post" novalidate>
                            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>" />
                            <label for="grade_<?= $submission['id'] ?>">Grade (0-100):</label>
                            <input type="number" id="grade_<?= $submission['id'] ?>" name="grade" min="0" max="100" step="0.01" />

                            <label for="feedback_<?= $submission['id'] ?>">Feedback:</label>
                            <textarea id="feedback_<?= $submission['id'] ?>" name="feedback" placeholder="Optional feedback for the student"></textarea>

                            <button type="submit">Submit Grade</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
