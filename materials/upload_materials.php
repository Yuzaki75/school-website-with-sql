<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch profile picture
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

$profilePic = empty($profile_pic_db) ? '../uploads/profile/default.png' : '../uploads/profile/' . basename($profile_pic_db);

// Handle file upload
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['material_file'])) {
    $subject_id = (int)$_POST['subject_id'];
    $file = $_FILES['material_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/materials/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_path = $upload_dir . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Save to database (assuming a materials table, but since not in schema, just message)
            $message = '<div class="alert alert-success">Material uploaded successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error uploading file.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">File upload error.</div>';
    }
}

// Fetch subjects assigned to teacher
$result = $conn->query("SELECT s.id, s.subject_name FROM subjects s JOIN student_subjects ss ON s.id = ss.subject_id WHERE ss.student_id IN (SELECT student_id FROM student_subjects WHERE subject_id = s.id) GROUP BY s.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Upload Materials</title>
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
    <h1>Upload Materials</h1>
    <?= $message ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label text-white">Subject</label>
            <select name="subject_id" class="form-select" required>
                <option value="">Select Subject</option>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['subject_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label text-white">Material File</label>
            <input type="file" name="material_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="../dashboard_teacher.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
