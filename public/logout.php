<?php
session_start();  // Start the session

// Destroy all session variables
session_unset();  // This clears all session variables

// Destroy the session
session_destroy();  // This removes the session data from the server

// Redirect the user to the login page
header("Location: login.html");  // Change to your login page
exit;
?>
