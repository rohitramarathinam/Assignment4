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
$tripType = $_GET['tripType'];
$origin = $_GET['origin'];
$destination = $_GET['destination'];
$departureDate = $_GET['departureDate'];
$returnDate = $_GET['returnDate'];
$totalPassengers = $_GET['totalPassengers'];

// Function to format the date to Y-m-d
function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

// Prepare the SQL query for the outbound flights
$outboundQuery = "SELECT * FROM flights WHERE origin = ? AND destination = ? AND departure_date = ? AND available_seats >= ?";

// Prepare the SQL query for the return flights if it's a round trip
$returnQuery = "SELECT * FROM flights WHERE origin = ? AND destination = ? AND departure_date = ? AND available_seats >= ?";

try {
    // Prepare and execute the query for outbound flights
    $stmt = $db->prepare($outboundQuery);
    $stmt->bind_param("sssi", $origin, $destination, $departureDate, $totalPassengers);
    $stmt->execute();
    $outboundFlights = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // If no outbound flights are found, check 3 days before or after the departure date
    if (empty($outboundFlights)) {
        $outboundFlights = [];

        // Calculate 3 days before and 3 days after the departure date
        $threeDaysBefore = date('Y-m-d', strtotime($departureDate . ' -3 days'));
        $threeDaysAfter = date('Y-m-d', strtotime($departureDate . ' +3 days'));

        // Modify the query to look for flights in the date range
        $outboundQueryWithRange = "SELECT * FROM flights WHERE origin = ? AND destination = ? AND departure_date BETWEEN ? AND ? AND available_seats >= ?";
        $stmt = $db->prepare($outboundQueryWithRange);
        $stmt->bind_param("ssssi", $origin, $destination, $threeDaysBefore, $threeDaysAfter, $totalPassengers);
        $stmt->execute();
        $outboundFlights = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Handle the return flights if the trip is round-trip
    $returnFlights = [];
    if ($tripType == "round-trip") {
        $stmt = $db->prepare($returnQuery);
        $stmt->bind_param("sssi", $destination, $origin, $returnDate, $totalPassengers);
        $stmt->execute();
        $returnFlights = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // If no return flights are found, check 3 days before or after the return date
        if (empty($returnFlights)) {
            $returnFlights = [];

            // Calculate 3 days before and 3 days after the return date
            $threeDaysBeforeReturn = date('Y-m-d', strtotime($returnDate . ' -3 days'));
            $threeDaysAfterReturn = date('Y-m-d', strtotime($returnDate . ' +3 days'));

            // Modify the query to look for return flights in the date range
            $returnQueryWithRange = "SELECT * FROM flights WHERE origin = ? AND destination = ? AND departure_date BETWEEN ? AND ? AND available_seats >= ?";
            $stmt = $db->prepare($returnQueryWithRange);
            $stmt->bind_param("ssssi", $destination, $origin, $threeDaysBeforeReturn, $threeDaysAfterReturn, $totalPassengers);
            $stmt->execute();
            $returnFlights = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Return the flights as JSON
    echo json_encode([
        'outboundFlights' => $outboundFlights,
        'returnFlights' => $returnFlights
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to retrieve flights: ' . $e->getMessage()]);
}
?>
