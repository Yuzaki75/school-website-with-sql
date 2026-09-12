<?php
// users/delete_user.php
session_start();
include '../config/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Validate and sanitize input
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: manage_users.php");
    exit();
}

// Check if trying to delete self
if ($id === $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: manage_users.php");
    exit();
}

// Delete the user
$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = "User deleted successfully.";
header("Location: manage_users.php");
exit();
