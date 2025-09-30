<?php
// users/edit_user.php
include __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the user
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    $update = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
    $update->bind_param("sssi", $username, $email, $role, $id);
    $update->execute();
    $update->close();

    header("Location: manage_users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
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
      -webkit-backdrop-filter: blur(6px);
      background-color: rgba(0, 0, 0, 0.3);
      z-index: -1;
    }

    .manage-box {
      background-color: rgba(0, 0, 0, 0.6);
      border-radius: 10px;
      padding: 20px;
      color: #fff;
    }

    h2 {
      color: #fff;
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
    <span class="username">Edit User</span>
    <a href="manage_users.php" class="profile-link">Back</a>
  </div>
</header>

<div class="container py-4">
  <div class="manage-box">
    <h2 class="mb-4">Edit User</h2>

    <form method="post">
      <div class="mb-3">
        <label class="form-label text-white">Username</label>
        <input type="text" name="username" class="form-control"
               value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label text-white">Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label text-white">Role</label>
        <select name="role" class="form-select" required>
          <option value="student" <?= ($user['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
          <option value="teacher" <?= ($user['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
          <option value="administrator" <?= ($user['role'] ?? '') === 'administrator' ? 'selected' : '' ?>>Administrator</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>

</body>
</html>
