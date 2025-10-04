<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch profile picture
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

$profilePic = empty($profile_pic_db) ? '../uploads/profile/default.png' : '../uploads/profile/' . basename($profile_pic_db);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $target_role = $_POST['target_role'] ?? 'all';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '' || $content === '') {
        $message = '<div class="alert alert-danger">Title and content are required.</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, posted_by, target_role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("sssis", $title, $content, $user_id, $target_role, $is_active);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Announcement added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error adding announcement: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert alert-danger">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Announcement</title>
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
            color: #fff;
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
    <h2>Add Announcement</h2>
    <?= $message ?>
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title *</label>
            <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Content *</label>
            <textarea id="content" name="content" class="form-control" rows="6" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="target_role" class="form-label">Target Role</label>
            <select id="target_role" name="target_role" class="form-select">
                <option value="all" <?= (($_POST['target_role'] ?? '') === 'all') ? 'selected' : '' ?>>All</option>
                <option value="admin" <?= (($_POST['target_role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                <option value="teacher" <?= (($_POST['target_role'] ?? '') === 'teacher') ? 'selected' : '' ?>>Teacher</option>
                <option value="student" <?= (($_POST['target_role'] ?? '') === 'student') ? 'selected' : '' ?>>Student</option>
            </select>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" id="is_active" name="is_active" class="form-check-input" <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?> />
            <label for="is_active" class="form-check-label">Active</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Add Announcement</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
