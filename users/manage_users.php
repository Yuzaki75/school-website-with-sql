<?php
// users/manage_users.php
include __DIR__ . '/../config/db.php';

// fetch users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- your styles -->
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

    /* Container box */
    .manage-box {
      background-color: rgba(0, 0, 0, 0.6);
      border-radius: 10px;
      padding: 20px;
      color: #fff;
    }

    h2 {
      color: #fff;
    }

    /* Table tweaks */
    .table {
      background-color: #fff;
      border-radius: 8px;
      overflow: hidden;
    }
    .table thead {
      background-color: #343a40;
      color: #fff;
    }
    .table tbody tr:hover {
      background-color: #f8f9fa;
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
    <span class="username">Manage Users</span>
    <a href="../admin_dashboard.php" class="profile-link">Back</a>
  </div>
</header>

<div class="container py-4">
  <div class="manage-box">
    <h2 class="mb-4">Manage Users</h2>

    <div class="mb-3">
      <a href="add_user.php" class="btn btn-success">+ Add New User</a>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username / Name</th>
            <th>Email</th>
            <th>Role</th>
            <th style="width:170px">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()):
          $id = $row['id'] ?? '';
          $username = $row['username'] ?? $row['name'] ?? '(no username)';
          $email = $row['email'] ?? '';
          $role = $row['role'] ?? '';
        ?>
          <tr>
            <td><?= htmlspecialchars($id) ?></td>
            <td><?= htmlspecialchars($username) ?></td>
            <td><?= htmlspecialchars($email) ?></td>
            <td><?= htmlspecialchars($role) ?></td>
            <td>
              <a href="edit_user.php?id=<?= urlencode($id) ?>" class="btn btn-sm btn-primary">Edit</a>
              <a href="change_password_user.php?id=<?= urlencode($id) ?>" class="btn btn-sm btn-warning">Change Password</a>
              <a href="delete_user.php?id=<?= urlencode($id) ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this user?');">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
