<?php
session_start();
include '../config/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id == 0) {
    header("Location: ../login.php");
    exit;
}

$stmt = $conn->prepare("SELECT username, full_name, email, role, profile_pic, created_at FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Default profile picture if none
$photo = !empty($user['profile_pic']) ? '../uploads/profile/'.$user['profile_pic'] : '../uploads/profile/default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css">
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
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(6px);
      background-color: rgba(0, 0, 0, 0.5);
      z-index: -1;
    }
    .profile-box {
      background: rgba(0,0,0,0.5);
      color: #fff;
      padding: 30px;
      border-radius: 10px;
      max-width: 500px;
      margin: 40px auto;
      text-align: center;
    }
    .profile-box img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

<header class="dashboard-header">
  <div class="header-left">
    <img src="../uploads/logo.png" class="school-logo" alt="logo">
    <span class="school-name">Dominican College of Santa Rosa</span>
  </div>
  <div class="header-right">
    <span class="username"><?= htmlspecialchars($user['username']); ?></span>
    <a href="../logout.php" class="profile-link">Logout</a>
  </div>
</header>

<div class="profile-box">
  <img src="<?= htmlspecialchars($photo); ?>" alt="Profile Picture">
  <h3><?= htmlspecialchars($user['full_name']); ?></h3>
  <p><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
  <p><strong>Role:</strong> <?= htmlspecialchars($user['role']); ?></p>
  <p><strong>Joined:</strong> <?= htmlspecialchars($user['created_at']); ?></p>
  <a href="edit_profile.php" class="btn btn-primary mt-3">Edit Profile</a>
  <a href="change_password.php" class="btn btn-secondary mt-3">Change Password</a>
</div>

</body>
</html>
