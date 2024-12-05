<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php'; // Include your database connection

// Get the JSON payload from the request body
$payload = json_decode(file_get_contents('php://input'), true);
// Example fix for logging an array to error_log
error_log("Received data: " . print_r($payload, true)); // Converting array to string

// Ensure payload is valid
if (!$payload) {
    echo json_encode(['error' => 'Invalid request']);
    http_response_code(400);
    exit;
}

// Extract the details from the payload
$flightID = $payload['departing']['flight-id'];
$passengers = $payload['passengers'];
$price = $payload['departing']['price'];
$totalPrice = calculateTotalPrice($price, $payload['departing']['adults'], $payload['departing']['children'], $payload['departing']['infants']);

// Generate a unique flight booking ID
$flightBookingID = $payload['departing']['booking-id'];

// Insert the flight booking into `flight_booking` table
$sql = "INSERT INTO flight_booking (flight_booking_id, flight_id, total_price) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssd", $flightBookingID, $flightID, $totalPrice);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to insert flight booking']);
    http_response_code(500);
    exit;
}

// Insert passengers into the `passenger` table and `tickets` table
foreach ($passengers as $passenger) {
    $ssn = $passenger['SSN'];
    $firstName = $passenger['first-name'];
    $lastName = $passenger['last-name'];
    $dob = $passenger['date-of-birth'];
    $category = $passenger['category'];

    // Insert passenger into the `passenger` table
    $sql_passenger = "INSERT INTO passenger (ssn, first_name, last_name, date_of_birth, category) VALUES (?, ?, ?, ?, ?)";
    $stmt_passenger = $conn->prepare($sql_passenger);
    $stmt_passenger->bind_param("sssss", $ssn, $firstName, $lastName, $dob, $category);

    if (!$stmt_passenger->execute()) {
        echo json_encode(['error' => 'Failed to insert passenger']);
        http_response_code(500);
        exit;
    }

    // Insert ticket into the `tickets` table
    $ticketID = generateUUID();  // Generate a unique ticket ID
    $ind_price = calculateTicketPrice($category, $price);  // Add a function to calculate ticket price based on category

    $sql_ticket = "INSERT INTO tickets (ticket_id, flight_booking_id, ssn, price) VALUES (?, ?, ?, ?)";
    $stmt_ticket = $conn->prepare($sql_ticket);
    $stmt_ticket->bind_param("ssss", $ticketID, $flightBookingID, $ssn, $ind_price);

    if (!$stmt_ticket->execute()) {
        echo json_encode(['error' => 'Failed to insert ticket']);
        http_response_code(500);
        exit;
    }
}

if ($payload["returning"]) {
    // Extract the details for the returning flight
    $returnFlightID = $payload["returning"]['flight-id'];
    $returnPrice = $payload["returning"]['price'];
    $returnTotalPrice = calculateTotalPrice($returnPrice, $payload["returning"]['adults'], $payload["returning"]['children'], $payload["returning"]['infants']);
    $returnFlightBookingID = $payload["returning"]['booking-id'];

    // Insert the flight booking into `flight_booking` table
    $sql = "INSERT INTO flight_booking (flight_booking_id, flight_id, total_price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssd", $returnFlightBookingID, $returnFlightID, $returnTotalPrice);

    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Failed to insert return flight booking']);
        http_response_code(500);
        exit;
    }

    // Insert return flight tickets for each passenger
    foreach ($passengers as $passenger) {
        $ssn = $passenger['SSN'];
        $category = $passenger['category']; // Category is part of each passenger in payload

        // Insert ticket into the `tickets` table for the return flight
        $returnTicketID = generateUUID();  // Generate a unique ticket ID
        $returnIndPrice = calculateTicketPrice($category, $returnPrice);

        $sql_ticket = "INSERT INTO tickets (ticket_id, flight_booking_id, ssn, price) VALUES (?, ?, ?, ?)";
        $stmt_ticket = $conn->prepare($sql_ticket);
        $stmt_ticket->bind_param("ssss", $returnTicketID, $returnFlightBookingID, $ssn, $returnIndPrice);

        if (!$stmt_ticket->execute()) {
            echo json_encode(['error' => 'Failed to insert return ticket']);
            http_response_code(500);
            exit;
        }
    }
}


// Success response
echo json_encode(['message' => 'Booking successful']);
http_response_code(200);

// Function to generate a unique UUID (simplified)
function generateUUID() {
    return strtoupper(bin2hex(random_bytes(16)));
}

// Function to calculate the ticket price based on category
function calculateTicketPrice($category, $price) {
    switch ($category) {
        case 'adults':
            return $price;
        case 'children':
            return $price * 0.7;  // Children get 70% of the price
        case 'infants':
            return $price * 0.1;  // Infants get 10% of the price
        default:
            return 0;
    }
}

function calculateTotalPrice($price, $adults, $children, $infants) {
    return ($adults * $price) + (0.7 * $children * $price) + (0.1 * $infants * $price);
}

?>

