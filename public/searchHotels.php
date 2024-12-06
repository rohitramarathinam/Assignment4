<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = ""; // after cloning, enter your password here if you have one setup
$dbname = ""; // after cloning, enter your db name here if you have one setup

// Create the connection
$db = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$is_logged_in = isset($_SESSION['first_name']);
if (!$is_logged_in) {
    header("Location: login.html");
    exit;
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Collect the input parameters from the request
$city = $_GET['city'];

// Prepare the SQL query to search for hotels in the city
$hotelQuery = "SELECT * FROM hotels WHERE city = ?";

try {
    // Prepare and execute the query for hotels in the city
    $stmt = $db->prepare($hotelQuery);
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $hotels = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Return the list of hotels as JSON
    header('Content-Type: application/json'); // Ensure JSON is being returned
    echo json_encode([
        'hotels' => $hotels
    ]);

} catch (Exception $e) {
    // If something goes wrong, send an error message
    echo json_encode(['error' => 'Failed to retrieve hotels: ' . $e->getMessage()]);
}
?>
