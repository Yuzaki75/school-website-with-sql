<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id'];

// Get current user info
$stmt = $conn->prepare("SELECT username, full_name, email, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $profile_pic = $user['profile_pic']; // keep existing by default

    // Validate inputs
    if (empty($username)) {
        $message = '<div class="alert alert-danger">Username cannot be empty.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Invalid email address.</div>';
    } else {
        // Handle upload
        if (!empty($_FILES['profile_pic']['name'])) {
            $file_name = time().'_'.basename($_FILES['profile_pic']['name']);
            $uploadDir = __DIR__ . '/../uploads/profile/'; // absolute path on disk
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // create folder if missing
            }
            $target = $uploadDir . $file_name;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                $profile_pic = $file_name;
            } else {
                $message = '<div class="alert alert-danger">File upload failed.</div>';
            }
        }

        // Update DB
        $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, email=?, profile_pic=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $full_name, $email, $profile_pic, $id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Profile updated successfully.</div>';
            // Refresh user info
            $user['username'] = $username;
            $user['full_name'] = $full_name;
            $user['email'] = $email;
            $user['profile_pic'] = $profile_pic;
        } else {
            $message = '<div class="alert alert-danger">Error updating profile: '.$stmt->error.'</div>';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
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
      background-color: rgba(0, 0, 0, 0.5);
      z-index: -1;
    }
    .edit-box {
      background-color: rgba(0, 0, 0, 0.5);
      border-radius: 10px;
      padding: 20px;
      max-width: 500px;
      margin: 50px auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }
    .profile-pic {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      display: block;
      margin: 0 auto 15px;
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
    <a href="view_profile.php" class="btn btn-light btn-sm">View Profile</a>
    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</header>

<div class="edit-box">
  <h2 class="text-center mb-3">Edit Profile</h2>
  <?= $message; ?>
  <?php
    $photo = '../uploads/profile/' . ($user['profile_pic'] ?: 'default.png');
  ?>
  <img src="<?= htmlspecialchars($photo) ?>" class="profile-pic" alt="Profile Picture">

  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Profile Picture</label>
      <input type="file" name="profile_pic" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
  </form>
</div>

</body>
</html>
