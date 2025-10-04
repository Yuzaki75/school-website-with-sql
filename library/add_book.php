<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch profile picture
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic_db);
$stmt->fetch();
$stmt->close();

$profilePic = empty($profile_pic_db) ? '../uploads/profile/default.png' : '../uploads/profile/' . basename($profile_pic_db);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_title = trim($_POST['book_title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $total_copies = (int)($_POST['total_copies'] ?? 1);
    $description = trim($_POST['description'] ?? '');

    if ($book_title === '' || $author === '') {
        $message = '<div class="alert alert-danger">Book title and author are required.</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO books (book_title, author, isbn, category, total_copies, available_copies, description, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssisi", $book_title, $author, $isbn, $category, $total_copies, $total_copies, $description, $user_id);
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Book added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error adding book: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert alert-danger">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Book</title>
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            background-color: rgba(0, 0, 0, 0.3);
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
            max-width: 700px;
            background: rgba(0,0,0,0.6);
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        label {
            color: #fff;
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
    <h2>Add Book</h2>
    <?= $message ?>
    <form method="post">
        <div class="mb-3">
            <label for="book_title" class="form-label">Book Title *</label>
            <input type="text" id="book_title" name="book_title" class="form-control" required value="<?= htmlspecialchars($_POST['book_title'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label for="author" class="form-label">Author *</label>
            <input type="text" id="author" name="author" class="form-control" required value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label for="isbn" class="form-label">ISBN</label>
            <input type="text" id="isbn" name="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" id="category" name="category" class="form-control" value="<?= htmlspecialchars($_POST['category'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label for="total_copies" class="form-label">Total Copies</label>
            <input type="number" id="total_copies" name="total_copies" class="form-control" min="1" value="<?= htmlspecialchars($_POST['total_copies'] ?? '1') ?>" />
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Add Book</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
