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

// Fetch user's borrow records
$stmt = $conn->prepare("SELECT br.id, b.book_title, b.author, br.borrow_date, br.due_date, br.status FROM borrow_records br JOIN books b ON br.book_id = b.id WHERE br.borrower_id = ? ORDER BY br.borrow_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$borrows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Borrows</title>
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
        <h1>My Borrowed Books</h1>
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
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Book Title</th>
                <th>Author</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($borrows as $borrow): ?>
            <tr>
                <td><?= htmlspecialchars($borrow['book_title']) ?></td>
                <td><?= htmlspecialchars($borrow['author']) ?></td>
                <td><?= htmlspecialchars($borrow['borrow_date']) ?></td>
                <td><?= htmlspecialchars($borrow['due_date']) ?></td>
                <td>
                    <?php
                    $status = $borrow['status'];
                    $class = 'text-success';
                    if ($status === 'overdue') $class = 'text-danger';
                    elseif ($status === 'returned') $class = 'text-muted';
                    ?>
                    <span class="<?= $class ?>"><?= ucfirst($status) ?></span>
                </td>
                <td>
                    <?php if ($status === 'borrowed'): ?>
                        <a href="return_book.php?id=<?= urlencode($borrow['id']) ?>" class="btn btn-warning btn-sm">Return</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
