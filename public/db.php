<?php
// Database connection file
$host = 'localhost';
$user = 'root';
$password = ''; // after cloning, enter your password here if you have one setup
$dbname = ''; // after cloning, enter your db name here if you have one setup

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
