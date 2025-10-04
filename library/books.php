<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? '';

// Fetch profile picture
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

$profilePic = empty($profile_pic_db) ? '../uploads/profile/default.png' : '../uploads/profile/' . basename($profile_pic_db);

// Fetch all books
$result = $conn->query("SELECT id, book_title, author, isbn, category, total_copies, available_copies, description FROM books ORDER BY book_title ASC");

$message = '';
if (isset($_GET['success'])) {
    $message = '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
} elseif (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Library Books</title>
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
            max-width: 1200px;
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
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
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
        <h1>Library Books</h1>
        <?php
        $back_url = '';
        if ($role === 'admin') {
            $back_url = '../admin_dashboard.php';
        } elseif ($role === 'teacher') {
            $back_url = '../dashboard_teacher.php';
        } elseif ($role === 'student') {
            $back_url = '../dashboard_student.php';
        }
        ?>
        <a href="<?= $back_url ?>" class="btn btn-outline-light">Back to Dashboard</a>
    </div>
    <?= $message ?>
    <?php if ($role === 'admin' || $role === 'teacher'): ?>
        <div class="mb-3">
            <a href="add_book.php" class="btn btn-success">Add Book</a>
        </div>
    <?php endif; ?>
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Category</th>
                <th>Total Copies</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['book_title']) ?></td>
                <td><?= htmlspecialchars($row['author']) ?></td>
                <td><?= htmlspecialchars($row['isbn']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['total_copies']) ?></td>
                <td><?= htmlspecialchars($row['available_copies']) ?></td>
                <td>
                    <?php if ($role === 'student' && $row['available_copies'] > 0): ?>
                        <a href="borrow_book.php?id=<?= urlencode($row['id']) ?>" class="btn btn-primary btn-sm">Borrow</a>
                    <?php endif; ?>
                    <?php if ($role === 'admin' || $role === 'teacher'): ?>
                        <a href="edit_book.php?id=<?= urlencode($row['id']) ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="delete_book.php?id=<?= urlencode($row['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
