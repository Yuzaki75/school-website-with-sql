<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'] ?? 'User';

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
<header class="dashboard-header">
    <div class="header-left">
        <img src="../uploads/logo.png" alt="School Logo" class="school-logo">
        <span class="school-name">Dominican College of Santa Rosa</span>
    </div>
    <div class="header-right">
        <span class="username">Hello, <?php echo htmlspecialchars($username); ?></span>
        <a href="../profile/view_profile.php">
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" class="profile-avatar">
        </a>
        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</header>
