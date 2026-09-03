<?php
// messages/send_message.php
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

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
    $subject = trim($_POST['subject'] ?? '');
    $msg = trim($_POST['message'] ?? '');

    if ($receiver_id && $subject && $msg) {
        // Validate receiver exists and is not self
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND id != ?");
        $stmt->bind_param("ii", $receiver_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $user_id, $receiver_id, $subject, $msg);
            if ($stmt->execute()) {
                header("Location: sent.php?success=Message sent successfully");
                exit();
            } else {
                $message = '<div class="alert alert-danger">Error sending message.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Invalid receiver.</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="alert alert-danger">All fields are required.</div>';
    }
}

// Fetch all users except self for receiver using prepared statement
$stmt = $conn->prepare("SELECT id, username, full_name FROM users WHERE id != ? ORDER BY username");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Send Message</title>
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
    <h1>Send Message</h1>
    <?= $message ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label text-white">To</label>
            <select name="receiver_id" class="form-select" required>
                <option value="">Select Receiver</option>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['full_name']) ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label text-white">Subject</label>
            <input type="text" name="subject" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-white">Message</label>
            <textarea name="message" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
        <a href="inbox.php" class="btn btn-secondary">Back to Inbox</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
