<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in and is student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$assignment_id = $_GET['id'] ?? '';

if (empty($assignment_id)) {
    header("Location: assignments.php");
    exit();
}

$error = '';
$success = '';

// Check if assignment exists and student is enrolled in the subject
$stmt = $conn->prepare("
    SELECT a.id, a.title, a.description, a.due_date, a.file_path, s.subject_name, u.full_name as teacher_name
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN users u ON a.assigned_by = u.id
    JOIN student_subjects ss ON s.id = ss.subject_id
    WHERE a.id = ? AND ss.student_id = ?
");
$stmt->bind_param("ii", $assignment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: assignments.php");
    exit();
}

$assignment = $result->fetch_assoc();
$stmt->close();

// Check if student already submitted
$stmt = $conn->prepare("SELECT id, submission_file, submission_text, submitted_at FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?");
$stmt->bind_param("ii", $assignment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_submission = $result->fetch_assoc();
$stmt->close();

$already_submitted = !empty($existing_submission);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_submitted) {
    $submission_text = trim($_POST['submission_text'] ?? '');

    if (empty($submission_text) && (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK)) {
        $error = "Please provide either a file upload or text submission.";
    } else {
        $submission_file = null;

        // Handle file upload if provided
        if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/assignments/submissions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = time() . '_' . $user_id . '_' . basename($_FILES['submission_file']['name']);
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_path)) {
                $submission_file = 'uploads/assignments/submissions/' . $file_name;
            } else {
                $error = "Failed to upload file.";
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, submission_file, submission_text) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $assignment_id, $user_id, $submission_file, $submission_text);
            if ($stmt->execute()) {
                $success = "Assignment submitted successfully.";
                $already_submitted = true;
                // Refresh existing submission data
                $existing_submission = [
                    'submission_file' => $submission_file,
                    'submission_text' => $submission_text,
                    'submitted_at' => date('Y-m-d H:i:s')
                ];
            } else {
                $error = "Failed to submit assignment.";
            }
            $stmt->close();
        }
    }
}

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
    <title>Submit Assignment</title>
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
            max-width: 800px;
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
            margin-bottom: 10px;
        }
        .due-date {
            color: #ff6b6b;
            font-weight: bold;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        textarea, input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: none;
        }
        textarea {
            resize: vertical;
            min-height: 150px;
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
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .submitted-info {
            background: rgba(76, 175, 67, 0.2);
            border: 1px solid #4BB543;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .download-link {
            color: #007bff;
            text-decoration: none;
        }
        .download-link:hover {
            text-decoration: underline;
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
    <h2>Submit Assignment</h2>

    <div class="assignment-info">
        <div class="assignment-title"><?= htmlspecialchars($assignment['title']) ?></div>
        <div class="assignment-meta">
            Subject: <?= htmlspecialchars($assignment['subject_name']) ?> |
            Teacher: <?= htmlspecialchars($assignment['teacher_name']) ?> |
            Due: <span class="due-date"><?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?></span>
        </div>
        <?php if (!empty($assignment['description'])): ?>
            <p><strong>Description:</strong> <?= htmlspecialchars($assignment['description']) ?></p>
        <?php endif; ?>
        <?php if (!empty($assignment['file_path'])): ?>
            <p><strong>Assignment File:</strong> <a href="../<?= htmlspecialchars($assignment['file_path']) ?>" class="download-link" target="_blank">Download</a></p>
        <?php endif; ?>
    </div>

    <?php if ($already_submitted): ?>
        <div class="submitted-info">
            <h4>You have already submitted this assignment</h4>
            <p><strong>Submitted on:</strong> <?= date('M d, Y H:i', strtotime($existing_submission['submitted_at'])) ?></p>
            <?php if (!empty($existing_submission['submission_text'])): ?>
                <p><strong>Your submission:</strong></p>
                <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; margin: 10px 0;">
                    <?= nl2br(htmlspecialchars($existing_submission['submission_text'])) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($existing_submission['submission_file'])): ?>
                <p><strong>Submitted file:</strong> <a href="../<?= htmlspecialchars($existing_submission['submission_file']) ?>" class="download-link" target="_blank">Download your submission</a></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
            <label for="submission_text">Your Submission (Text):</label>
            <textarea id="submission_text" name="submission_text" placeholder="Write your assignment submission here..."></textarea>

            <label for="submission_file">Or Upload File:</label>
            <input type="file" id="submission_file" name="submission_file" accept=".pdf,.doc,.docx,.txt,.jpg,.png,.zip" />

            <p style="font-size: 14px; color: #ccc;">You can submit either text, a file, or both.</p>

            <button type="submit">Submit Assignment</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
