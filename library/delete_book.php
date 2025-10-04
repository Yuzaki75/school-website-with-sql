<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $book_id = (int)$_GET['id'];

    // Check if book exists and has no active borrows
    $stmt = $conn->prepare("SELECT COUNT(*) as active_borrows FROM borrow_records WHERE book_id = ? AND status = 'borrowed'");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->bind_result($active_borrows);
    $stmt->fetch();
    $stmt->close();

    if ($active_borrows > 0) {
        // Cannot delete if there are active borrows
        header("Location: books.php?error=Cannot delete book with active borrows");
        exit();
    }

    // Delete the book
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    if ($stmt->execute()) {
        header("Location: books.php?success=Book deleted successfully");
    } else {
        header("Location: books.php?error=Error deleting book");
    }
    $stmt->close();
} else {
    header("Location: books.php");
}

exit();
?>
