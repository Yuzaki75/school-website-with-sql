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

// Get some stats
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$available_books = $conn->query("SELECT SUM(available_copies) as count FROM books")->fetch_assoc()['count'];
$user_borrows = $conn->query("SELECT COUNT(*) as count FROM borrow_records WHERE borrower_id = $user_id AND status = 'borrowed'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Library</title>
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
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            margin: 0 10px;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 2em;
        }
        .stat-card p {
            margin: 5px 0 0;
        }
        .actions {
            text-align: center;
        }
        .actions .btn {
            margin: 10px;
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
        <h1>Library</h1>
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

    <div class="stats">
        <div class="stat-card">
            <h3><?= $total_books ?></h3>
            <p>Total Books</p>
        </div>
        <div class="stat-card">
            <h3><?= $available_books ?></h3>
            <p>Available Books</p>
        </div>
        <div class="stat-card">
            <h3><?= $user_borrows ?></h3>
            <p>My Borrowed Books</p>
        </div>
    </div>

    <div class="actions">
        <a href="books.php" class="btn btn-primary">Browse Books</a>
        <a href="my_borrows.php" class="btn btn-secondary">My Borrows</a>
        <?php if ($role === 'admin' || $role === 'teacher'): ?>
            <a href="add_book.php" class="btn btn-success">Add Book</a>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
