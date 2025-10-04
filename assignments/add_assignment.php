<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in and is teacher or admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $subject_id = $_POST['subject_id'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $assigned_by = $_SESSION['user_id'];

    if (empty($title) || empty($subject_id) || empty($due_date)) {
        $error = "Title, subject, and due date are required.";
    } else {
        // Handle file upload if provided
        $file_path = null;
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/assignments/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = time() . '_' . basename($_FILES['assignment_file']['name']);
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_path)) {
                $file_path = 'uploads/assignments/' . $file_name;
            } else {
                $error = "Failed to upload file.";
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO assignments (title, description, subject_id, assigned_by, due_date, file_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiiss", $title, $description, $subject_id, $assigned_by, $due_date, $file_path);
            if ($stmt->execute()) {
                $success = "Assignment added successfully.";
                // Reset form
                $title = $description = $subject_id = $due_date = '';
            } else {
                $error = "Failed to add assignment.";
            }
            $stmt->close();
        }
    }
}

// Fetch subjects for the current teacher or all subjects for admin
if ($_SESSION['role'] === 'teacher') {
    // For teachers, get subjects they are assigned to teach
    $stmt = $conn->prepare("
        SELECT DISTINCT s.id, s.subject_name, s.subject_code
        FROM subjects s
        JOIN student_subjects ss ON s.id = ss.subject_id
        JOIN users u ON ss.student_id = u.id
        WHERE u.id IN (
            SELECT student_id FROM student_subjects WHERE subject_id = s.id
        )
        ORDER BY s.subject_name ASC
    ");
} else {
    // For admin, get all subjects
    $stmt = $conn->prepare("SELECT id, subject_name, subject_code FROM subjects ORDER BY subject_name ASC");
}

$stmt->execute();
$result = $stmt->get_result();
$subjects = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Add Assignment</title>
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
            display: block;
            margin-top: 15px;
        }
        input[type="text"], textarea, select, input[type="datetime-local"], input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: none;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
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
    <a href="assignments.php" class="btn btn-outline-light mb-3">&larr; Back to Assignments</a>
    <h2>Add New Assignment</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" novalidate>
        <label for="title">Assignment Title:</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required />

        <label for="description">Description:</label>
        <textarea id="description" name="description" placeholder="Optional description of the assignment"><?= htmlspecialchars($description ?? '') ?></textarea>

        <label for="subject_id">Subject:</label>
        <select id="subject_id" name="subject_id" required>
            <option value="">Select Subject</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id'] ?>" <?= ($subject_id ?? '') == $subject['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="due_date">Due Date:</label>
        <input type="datetime-local" id="due_date" name="due_date" value="<?= htmlspecialchars($due_date ?? '') ?>" required />

        <label for="assignment_file">Assignment File (optional):</label>
        <input type="file" id="assignment_file" name="assignment_file" accept=".pdf,.doc,.docx,.txt,.jpg,.png" />

        <button type="submit">Add Assignment</button>
    </form>
</div>
</body>
</html>
