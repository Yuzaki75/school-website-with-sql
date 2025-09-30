<?php
include __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? 0;

// Fetch course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE courses SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    header("Location: courses.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
    <h2>Edit Course</h2>
    <form method="post">
        <label>Course Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($course['name']); ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
