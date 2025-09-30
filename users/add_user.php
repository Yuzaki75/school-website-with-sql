<?php
// users/add_user.php
include __DIR__ . '/../config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $role      = $_POST['role'] ?? 'student';

    if ($username === '' || $password === '') {
        $message = '<div class="alert alert-danger text-center">Username and password are required.</div>';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role, created_at)
                                VALUES (?, ?, ?, ?, ?, NOW())");

        if ($stmt) {
            $stmt->bind_param("sssss", $username, $hashedPassword, $full_name, $email, $role);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success text-center">User added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger text-center">Error adding user: '.$stmt->error.'</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert alert-danger text-center">Prepare failed: '.$conn->error.'</div>';
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    /* Same blurred background as dashboards */
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
      max-width: 500px;
      margin: auto;
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
    <span class="username">Add User</span>
    <a href="manage_users.php" class="profile-link">Back</a>
  </div>
</header>

<div class="container py-4">
  <div class="manage-box">
    <h2 class="mb-4 text-center">Add New User</h2>
    <?= $message; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label text-white">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label text-white">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label text-white">Full Name</label>
        <input type="text" name="full_name" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label text-white">Email</label>
        <input type="email" name="email" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label text-white">Role</label>
        <select name="role" class="form-select" required>
          <option value="administrator">Administrator</option>
          <option value="teacher">Teacher</option>
          <option value="student">Student</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100">Add User</button>
    </form>
  </div>
</div>

</body>
</html>
