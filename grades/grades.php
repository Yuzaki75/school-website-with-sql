<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

if ($role === 'student') {
    $stmt = $conn->prepare("SELECT g.id, c.course_name, s.subject_name, g.grade
                            FROM grades g
                            JOIN subjects s ON g.subject_id = s.id
                            LEFT JOIN courses c ON s.course_id = c.id
                            WHERE g.student_id = ?");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT g.id, u.full_name, c.course_name, s.subject_name, g.grade
                            FROM grades g
                            JOIN users u ON g.student_id = u.id
                            JOIN subjects s ON g.subject_id = s.id
                            LEFT JOIN courses c ON s.course_id = c.id");
}

$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$username = $_SESSION['username'];

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Grades</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .btn {
            margin-right: 5px;
        }
        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: rgba(0,0,0,0.8);
            color: #fff;
            text-align: center;
            padding: 10px;
            z-index: 10;
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Grades</h2>
        <?php
        $back_url = '';
        if ($role === 'student') {
            $back_url = '../dashboard_student.php';
        } elseif ($role === 'teacher') {
            $back_url = '../dashboard_teacher.php';
        } elseif ($role === 'admin') {
            $back_url = '../admin_dashboard.php';
        }
        if ($back_url): ?>
            <a href="<?= $back_url ?>" class="btn btn-outline-light">&larr; Back to Dashboard</a>
        <?php endif; ?>
    </div>
    <?php if (empty($grades)): ?>
        <p>No grades found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <?php if ($role !== 'student'): ?>
                    <th>Student</th>
                    <?php endif; ?>
                    <th>Course</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <?php if ($role === 'admin' || $role === 'teacher'): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $grade): ?>
                <tr>
                    <?php if ($role !== 'student'): ?>
                    <td><?= htmlspecialchars($grade['full_name']) ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($grade['course_name']) ?></td>
                    <td><?= htmlspecialchars($grade['subject_name']) ?></td>
                    <td><?= htmlspecialchars($grade['grade']) ?></td>
                    <?php if ($role === 'admin' || $role === 'teacher'): ?>
                    <td>
                        <a href="edit_grade.php?id=<?= $grade['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="delete_grade.php?id=<?= $grade['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this grade?');">Delete</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
