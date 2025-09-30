<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: manage_users.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new === '') {
        $message = '<div class="alert alert-danger">New password cannot be empty.</div>';
    } elseif ($new !== $confirm) {
        $message = '<div class="alert alert-danger">New passwords do not match.</div>';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $newHash, $user_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Password changed successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating password: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change User Password</title>
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
  <div class="header-right">
    <span class="username">Change User Password</span>
    <a href="manage_users.php" class="btn btn-light btn-sm">Back</a>
  </div>
</header>

<div class="container py-4">
  <div class="profile-box">
    <h2 class="mb-4">Change Password for User ID: <?= htmlspecialchars($user_id) ?></h2>
    <?= $message; ?>
    <form method="post">
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
  </div>
</div>
</body>
</html>
