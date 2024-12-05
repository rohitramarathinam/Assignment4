<?php
// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php'; // Include your database connection

// Path to the JSON file
$jsonFile = 'hotels-info.json';

// Check if the file exists
if (!file_exists($jsonFile)) {
    echo json_encode(['error' => 'File not found!']);
    http_response_code(400);
    exit;
}

// Load the JSON file into a string
$jsonString = file_get_contents($jsonFile);

// Decode the JSON string into a PHP array
$data = json_decode($jsonString, true);

// Check if decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Error decoding JSON: ' . json_last_error_msg()]);
    http_response_code(400);
    exit;
}

// Check if the JSON contains hotels data
if (!isset($data['hotels'])) {
    echo json_encode(['error' => 'No hotels found in the JSON.']);
    http_response_code(400);
    exit;
}

// Prepare the SQL statement to insert hotel data
$sql = "INSERT INTO hotels (hotel_id, hotel_name, city, price_per_night) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        hotel_name = VALUES(hotel_name),
        city = VALUES(city),
        price_per_night = VALUES(price_per_night)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Database preparation failed: ' . $conn->error]);
    http_response_code(500);
    exit;
}

// Loop through each hotel and insert into the database
foreach ($data['hotels'] as $hotel) {
    $hotelID = $hotel['hotel-id'];
    $hotelName = $hotel['hotel-name'];
    $city = $hotel['city'];
    $pricePerNight = $hotel['price-per-night'];

    // Bind parameters and execute the statement
    $stmt->bind_param("sssd", $hotelID, $hotelName, $city, $pricePerNight);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Failed to insert hotel: ' . $hotelName]);
        http_response_code(500);
        exit;
    }
}

// Close the connection
$stmt->close();
$conn->close();
?>
