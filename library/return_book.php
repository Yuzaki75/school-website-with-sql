<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$message = '';

if (isset($_GET['id'])) {
    $borrow_id = (int)$_GET['id'];

    // Check if the borrow record belongs to the user and is borrowed
    $stmt = $conn->prepare("SELECT book_id FROM borrow_records WHERE id = ? AND borrower_id = ? AND status = 'borrowed'");
    $stmt->bind_param("ii", $borrow_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($book_id);
    $stmt->fetch();
    $stmt->close();

    if ($book_id) {
        // Return the book
        $return_date = date('Y-m-d');
        $status = 'returned';

        $stmt = $conn->prepare("UPDATE borrow_records SET return_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $return_date, $status, $borrow_id);
        if ($stmt->execute()) {
            // Update available copies
            $conn->query("UPDATE books SET available_copies = available_copies + 1 WHERE id = $book_id");
            $message = '<div class="alert alert-success">Book returned successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error returning book: ' . htmlspecialchars($stmt->error) . '</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="alert alert-danger">Invalid borrow record or book already returned.</div>';
    }
} else {
    $message = '<div class="alert alert-danger">Invalid borrow ID.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Return Book</title>
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
    <h2>Return Book</h2>
    <?= $message ?>
    <a href="my_borrows.php" class="btn btn-secondary">Back to My Borrows</a>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
