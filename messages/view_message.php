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

if (isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];

    // Fetch message
    $stmt = $conn->prepare("SELECT m.subject, m.message, m.created_at, m.is_read, u.username as sender FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.id = ? AND m.receiver_id = ?");
    $stmt->bind_param("ii", $message_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $msg = $result->fetch_assoc();

        // Mark as read if not already
        if (!$msg['is_read']) {
            $update_stmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE id = ?");
            $update_stmt->bind_param("i", $message_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    } else {
        header("Location: inbox.php?error=Message not found");
        exit();
    }
    $stmt->close();
} else {
    header("Location: inbox.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View Message</title>
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
        h1 {
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
    <h1>Message</h1>
    <div class="card bg-dark text-white">
        <div class="card-header">
            <strong>From:</strong> <?= htmlspecialchars($msg['sender']) ?><br>
            <strong>Subject:</strong> <?= htmlspecialchars($msg['subject']) ?><br>
            <strong>Date:</strong> <?= htmlspecialchars($msg['created_at']) ?>
        </div>
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
        </div>
    </div>
    <a href="inbox.php" class="btn btn-secondary mt-3">Back to Inbox</a>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
