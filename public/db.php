<?php
// Database connection file
$host = 'localhost';
$user = 'root';
$password = "Theguydownstairs1"; // after cloning, enter your password here if you have one setup
$dbname = "Assignment4"; // after cloning, enter your db name here if you have one setup

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
