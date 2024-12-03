<?php
// Database connection file
$host = 'localhost';
$user = 'root';
$password = '2Giant&Slayer'; // after cloning, enter your password here if you have one setup
$dbname = 'CS6314 Database Server'; // after cloning, enter your db name here if you have one setup

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
