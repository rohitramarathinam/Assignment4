<?php
session_start();
$is_logged_in = isset($_SESSION['first_name']);

if ($is_logged_in) {
    header("Location: home.php");
    exit;
}

$filePath = dirname(__FILE__) . '/login.html';

if (file_exists($filePath)) {
    readfile($filePath);
} else {
    echo "The file 'login.html' could not be found.";
}
?>
