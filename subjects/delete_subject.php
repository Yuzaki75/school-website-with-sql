<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in and is admin or teacher
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: subjects.php");
    exit();
}

$subject_id = (int)$_GET['id'];

// Delete the subject
$stmt = $conn->prepare("DELETE FROM subjects WHERE id=?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();

header("Location: subjects.php");
exit();
?>
