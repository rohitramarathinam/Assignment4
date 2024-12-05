<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php'; // Include your database connection

// Get the JSON payload from the request body
$payload = json_decode(file_get_contents('php://input'), true);
error_log("Received data: " . print_r($payload, true)); // Logging the received data

// Ensure payload is valid
if (!$payload) {
    echo json_encode(['error' => 'Invalid request']);
    http_response_code(400);
    exit;
}

// Extract details from the payload
$hotel = $payload['hotel'];
$bookingID = $payload['bookingID'];
$guests = $payload['guests'];

// Check if the hotel exists, and insert if it doesn't
$sql = "SELECT hotel_id FROM hotels WHERE hotel_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hotel['hotel-id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert the hotel details
    $sql_insert_hotel = "INSERT INTO hotels (hotel_id, hotel_name, city, price_per_night) VALUES (?, ?, ?, ?)";
    $stmt_insert_hotel = $conn->prepare($sql_insert_hotel);
    $stmt_insert_hotel->bind_param(
        "sssi",
        $hotel['hotel-id'],
        $hotel['hotel-name'],
        $hotel['city'],
        $hotel['price-per-night']
    );

    if (!$stmt_insert_hotel->execute()) {
        echo json_encode(['error' => 'Failed to insert hotel']);
        http_response_code(500);
        exit;
    }
}

// Calculate total price
$checkIn = new DateTime($hotel['checkIn']);
$checkOut = new DateTime($hotel['checkOut']);
$nights = $checkOut->diff($checkIn)->days;
$totalPrice = $hotel['rooms'] * $hotel['price-per-night'] * $nights;

// Insert the hotel booking
$sql_insert_booking = "INSERT INTO hotel_booking (hotel_booking_id, hotel_id, check_in, check_out, rooms, price_per_night, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt_insert_booking = $conn->prepare($sql_insert_booking);
$stmt_insert_booking->bind_param(
    "ssssiid",
    $bookingID,
    $hotel['hotel-id'],
    $hotel['checkIn'],
    $hotel['checkOut'],
    $hotel['rooms'],
    $hotel['price-per-night'],
    $totalPrice
);

if (!$stmt_insert_booking->execute()) {
    echo json_encode(['error' => 'Failed to insert booking']);
    http_response_code(500);
    exit;
}

// Insert guests
$sql_insert_guest = "INSERT INTO guests (ssn, first_name, last_name, date_of_birth, category, hotel_booking_id) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_insert_guest = $conn->prepare($sql_insert_guest);

foreach ($guests as $guest) {
    $stmt_insert_guest->bind_param(
        "ssssss",
        $guest['SSN'],
        $guest['first-name'],
        $guest['last-name'],
        $guest['date-of-birth'],
        $guest['category'],
        $bookingID
    );

    if (!$stmt_insert_guest->execute()) {
        echo json_encode(['error' => 'Failed to insert guest']);
        http_response_code(500);
        exit;
    }
}

// Success response
echo json_encode(['message' => 'Booking successful']);
http_response_code(200);
?>
