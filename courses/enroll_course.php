<?php
include __DIR__ . '/../config/db.php';

$student_id = $_GET['student_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;

if ($student_id && $course_id) {
    $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    header("Location: courses.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enroll in Course</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
    <h2>Enroll in Course</h2>
    <form method="get">
        <label>Student ID:</label>
        <input type="number" name="student_id" required>
        <label>Course ID:</label>
        <input type="number" name="course_id" required>
        <button type="submit">Enroll</button>
    </form>
</body>
</html>
