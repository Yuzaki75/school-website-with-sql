<?php
include __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: courses.php");
exit;
?>
