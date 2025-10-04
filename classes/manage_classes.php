<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Only admin and teacher can manage classes
if (!in_array($role, ['admin', 'teacher'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch profile picture
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

$profilePic = !empty($profile_pic_db)
    ? '../uploads/profile/' . basename($profile_pic_db)
    : '../uploads/profile/default.png';

// Fetch courses
$result = $conn->query("SELECT * FROM courses");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Classes</title>
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
            max-width: 900px;
            background: rgba(0,0,0,0.6);
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        h1 {
            margin-bottom: 20px;
        }
        .table thead {
            background-color: #343a40;
            color: #fff;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-sm {
            margin-right: 5px;
        }
    </style>
    <script>
        function confirmDelete(courseId) {
            if (confirm("Are you sure you want to delete this course?")) {
                window.location.href = "../courses/drop_course.php?id=" + courseId;
            }
        }
    </script>
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
    <h1>Manage Classes</h1>
    <a href="../courses/add_course.php" class="btn btn-success mb-3">➕ Add New Course</a>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Subjects</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                    <td>
                        <?php
                        $course_id = $row['id'];
                        $stmt_subj = $conn->prepare("SELECT s.subject_code, s.subject_name FROM subjects s JOIN course_subjects cs ON s.id = cs.subject_id WHERE cs.course_id = ?");
                        $stmt_subj->bind_param("i", $course_id);
                        $stmt_subj->execute();
                        $result_subj = $stmt_subj->get_result();
                        $subjects = [];
                        while ($subj = $result_subj->fetch_assoc()) {
                            $subjects[] = htmlspecialchars($subj['subject_code'] . ' - ' . $subj['subject_name']);
                        }
                        $stmt_subj->close();
                        echo implode("<br>", $subjects);
                        ?>
                    </td>
                    <td>
                        <a href="../courses/edit_course.php?id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-primary">✏️ Edit</a>
                        <a href="../courses/assign_subjects.php?id=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-secondary">📚 Assign Subjects</a>
                        <button onclick="confirmDelete(<?= htmlspecialchars($row['id']) ?>)" class="btn btn-sm btn-danger">❌ Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
