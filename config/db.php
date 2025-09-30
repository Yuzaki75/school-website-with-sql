<?php
// config/db.php
$host = "localhost";
$user = "root";        // your MySQL username
$pass = "";            // your MySQL password (XAMPP default is empty)
$dbname = "student_portal"; // your DB name

$conn = new mysqli($host, $user, $pass, $dbname);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
