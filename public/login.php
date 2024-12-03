<?php
session_start();  // Start the session to store user data if login is successful

// Database connection (update with your own credentials)
include 'db.php';  // Include your database connection (update the path as necessary)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input from the form
    $email = $_POST['email'];
    $password = $_POST['pwd'];

    // Query to get user from the database based on email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);  // "s" indicates that email is a string

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();
        
        // Check if the entered password matches the stored hashed password
        if (password_verify($password, $user['password'])) {
            // Password is correct, log the user in
            
            // Start a session and store user details
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone-no'] = $user['phone-no'];
            $_SESSION['gender'] = $user['gender'];

            
            // Redirect to home or dashboard page
            header("Location: home.php");  // Change to the appropriate page
            exit;
        } else {
            // Incorrect password
            echo "Incorrect password. Please try again.";
        }
    } else {
        // No user found with that email
        echo "No user found with that email address.";
    }

    // Close the prepared statement
    $stmt->close();
    $conn->close();
}
?>
