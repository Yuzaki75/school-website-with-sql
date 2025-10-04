<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? '';

// Fetch profile picture
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

$profilePic = empty($profile_pic_db) ? '../uploads/profile/default.png' : '../uploads/profile/' . basename($profile_pic_db);

// Fetch announcements visible to the user role or all
$stmt = $conn->prepare("SELECT a.id, a.title, a.content, u.full_name AS author, a.created_at 
                        FROM announcements a
                        JOIN users u ON a.posted_by = u.id
                        WHERE a.is_active = TRUE AND (a.target_role = 'all' OR a.target_role = ?)
                        ORDER BY a.created_at DESC");
$stmt->bind_param("s", $role);
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Announcements</title>
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
            max-width: 900px;
            background: rgba(0,0,0,0.6);
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        .announcement {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .announcement h3 {
            margin-top: 0;
        }
        .announcement .meta {
            font-size: 0.9em;
            color: #ccc;
            margin-bottom: 10px;
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
        <h2>Announcements</h2>
        <?php
        $back_url = '';
        if ($role === 'admin') {
            $back_url = '../admin_dashboard.php';
        } elseif ($role === 'teacher') {
            $back_url = '../dashboard_teacher.php';
        } elseif ($role === 'student') {
            $back_url = '../dashboard_student.php';
        }
        ?>
        <a href="<?= $back_url ?>" class="btn btn-outline-light">Back to Dashboard</a>
    </div>
    <?php if ($role === 'admin' || $role === 'teacher'): ?>
        <div class="mb-3">
            <a href="add_announcement.php" class="btn btn-success">Add Announcement</a>
        </div>
    <?php endif; ?>
    <?php if (empty($announcements)): ?>
        <p>No announcements found.</p>
    <?php else: ?>
        <?php foreach ($announcements as $announcement): ?>
            <div class="announcement">
                <h3><?= htmlspecialchars($announcement['title']) ?></h3>
                <div class="meta">Posted by <?= htmlspecialchars($announcement['author']) ?> on <?= htmlspecialchars($announcement['created_at']) ?></div>
                <p><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                <?php if ($role === 'admin' || $role === 'teacher'): ?>
                <div class="actions mt-2">
                    <a href="edit_announcement.php?id=<?= $announcement['id'] ?>" class="btn btn-sm btn-primary me-2">Edit</a>
                    <a href="delete_announcement.php?id=<?= $announcement['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this announcement?');">Delete</a>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
