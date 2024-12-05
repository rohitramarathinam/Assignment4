<?php
session_start();

// Check if the admin is logged in
// if (isset($_SESSION['phone-no'])!=="222-222-2222") {
//     header("Location: login.html");
//     exit;
// }

// Database connection details
$servername = "localhost";
$username = "root";
$password = "Theguydownstairs1"; // after cloning, enter your password here if you have one setup
$dbname = "Assignment4"; // after cloning, enter your db name here if you have one setup

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Path to the XML file
$xml_file = 'flights-info.xml';

// Load the XML file
$xml = simplexml_load_file($xml_file);
if ($xml === false) {
    die("Error loading XML file.");
}

// Iterate through each flight in the XML file and insert into the database
foreach ($xml->flight as $flight) {
    $flight_id = (string) $flight->{'flight-id'};
    $origin = (string) $flight->origin;
    $destination = (string) $flight->destination;
    $departure_date = (string) $flight->{'departure-date'};
    $arrival_date = (string) $flight->{'arrival-date'};
    $departure_time = (string) $flight->{'departure-time'};
    $arrival_time = (string) $flight->{'arrival-time'};
    $available_seats = (int) $flight->{'available-seats'};
    $price = (float) $flight->price;

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO flights (flight_id, origin, destination, departure_date, arrival_date, departure_time, arrival_time, available_seats, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssid", $flight_id, $origin, $destination, $departure_date, $arrival_date, $departure_time, $arrival_time, $available_seats, $price);

    // Execute the query
    if ($stmt->execute()) {
        echo "Flight $flight_id added successfully.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }
}

// Close the connection
$stmt->close();
$conn->close();
?>
