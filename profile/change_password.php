<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // fetch current hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($old, $hashed)) {
        $message = '<div class="alert alert-danger">Old password incorrect.</div>';
    } elseif ($new !== $confirm) {
        $message = '<div class="alert alert-danger">New passwords do not match.</div>';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $newHash, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = '<div class="alert alert-success">Password changed successfully.</div>';
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
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
      top: 0; left: 0;
      width: 100%; height: 100%;
      backdrop-filter: blur(6px);
      background-color: rgba(0, 0, 0, 0.3);
      z-index: -1;
    }
    .profile-box {
      background-color: rgba(0,0,0,0.6);
      color: #fff;
      border-radius: 10px;
      padding: 20px;
    }
    label {color:#fff;}
  </style>
</head>
<body>
<header class="dashboard-header">
  <div class="header-left">
    <img src="../uploads/logo.png" class="school-logo" alt="logo">
    <span class="school-name">Dominican College of Santa Rosa</span>
  </div>
</header>

<div class="container py-4">
  <div class="profile-box">
    <h2 class="mb-4">Change Password</h2>
    <?= $message; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Old Password</label>
        <input type="password" name="old_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success">Change Password</button>
    </form>
    <!-- back button -->
    <div class="mt-3 text-center">
      <a href="view_profile.php" class="btn btn-light">&larr; Back</a>
    </div>
  </div>
</div>
</body>
</html>
