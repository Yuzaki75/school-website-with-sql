<?php
// Database connection configuration

$servername = "localhost";  // Change if your DB server is different
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password
$dbname = "student_portal"; // The database name to create and use

// Create connection to MySQL server
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Now $conn can be used for queries on the student_portal database
?>
