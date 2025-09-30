<?php
session_start();
include __DIR__ . '/../config/db.php';

// Check if user is logged in and is admin or teacher
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$id = (int)$id;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: courses.php");
exit;
?>
