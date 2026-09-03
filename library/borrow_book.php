<?php
// library/borrow_book.php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$message = '';

if (isset($_GET['id'])) {
    $book_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($book_id) {
        // Check if book exists and has available copies using prepared statement
        $stmt = $conn->prepare("SELECT book_title, available_copies FROM library_books WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $stmt->bind_result($book_title, $available_copies);
        $stmt->fetch();
        $stmt->close();

        if ($book_title && $available_copies > 0) {
            // Check if user already borrowed this book and not returned
            $stmt = $conn->prepare("SELECT id FROM book_borrowings WHERE book_id = ? AND borrower_id = ? AND status = 'borrowed'");
            $stmt->bind_param("ii", $book_id, $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $message = '<div class="alert alert-warning">You have already borrowed this book and not returned it yet.</div>';
            } else {
                // Borrow the book
                $borrow_date = date('Y-m-d');
                $due_date = date('Y-m-d', strtotime('+14 days')); // 14 days due
                $status = 'borrowed';
                $issued_by = $user_id; // For simplicity, issued by self

                $stmt = $conn->prepare("INSERT INTO book_borrowings (book_id, borrower_id, borrow_date, due_date, status, issued_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisssi", $book_id, $user_id, $borrow_date, $due_date, $status, $issued_by);
                if ($stmt->execute()) {
                    // Update available copies using prepared statement
                    $stmt = $conn->prepare("UPDATE library_books SET available_copies = available_copies - 1 WHERE id = ?");
                    $stmt->bind_param("i", $book_id);
                    $stmt->execute();
                    $stmt->close();
                    $message = '<div class="alert alert-success">Book borrowed successfully! Due date: ' . htmlspecialchars($due_date) . '</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error borrowing book: ' . htmlspecialchars($stmt->error) . '</div>';
                }
                $stmt->close();
            }
        } else {
            $message = '<div class="alert alert-danger">Book not available or does not exist.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Invalid book ID.</div>';
    }
} else {
    $message = '<div class="alert alert-danger">Invalid book ID.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Borrow Book</title>
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
    <h2>Borrow Book</h2>
    <?= $message ?>
    <a href="books.php" class="btn btn-secondary">Back to Books</a>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
