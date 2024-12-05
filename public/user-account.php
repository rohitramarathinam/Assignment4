<?php
session_start();
include 'db.php';

// Function to fetch user information
function getUserInfo($userId) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to fetch flight booking by booking ID
function getFlightBookingInfo($flightBookingId) {
    global $conn;
    $sql = "SELECT * FROM flight_booking WHERE flight_booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $flightBookingId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to fetch hotel booking by booking ID
function getHotelBookingInfo($hotelBookingId) {
    global $conn;
    $sql = "SELECT * FROM hotel_booking WHERE hotel_booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $hotelBookingId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to fetch passengers for a flight booking
function getPassengersForFlight($flightBookingId) {
    global $conn;
    $sql = "SELECT p.first_name, p.last_name, p.ssn, t.ticket_id 
            FROM passenger p 
            JOIN tickets t ON p.ssn = t.ssn 
            WHERE t.flight_booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $flightBookingId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch all flights and hotels for September 2024
function getBookingsForSeptember2024() {
    global $conn;
    $sql = "SELECT * FROM flights JOIN flight_booking ON flight_booking.flight_id = flights.flight_id WHERE flights.departure_date BETWEEN '2024-09-01' AND '2024-09-30'
            UNION
            SELECT * FROM hotel_booking WHERE check_in BETWEEN '2024-09-01' AND '2024-09-30'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch flights for a specific person by SSN
function getFlightsBySSN($ssn) {
    global $conn;
    $sql = "SELECT * FROM flight_booking f
            JOIN tickets t ON f.flight_booking_id = t.flight_booking_id
            WHERE t.ssn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ssn);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$userId = $_SESSION['user_id'];
$user = getUserInfo($userId);

// Get form data for searching
$flightBookingId = isset($_POST['flight_booking_id']) ? $_POST['flight_booking_id'] : '';
$hotelBookingId = isset($_POST['hotel_booking_id']) ? $_POST['hotel_booking_id'] : '';
$ssn = isset($_POST['ssn']) ? $_POST['ssn'] : '';
$septemberBookings = isset($_POST['september_bookings']) ? true : false;

// Initialize variables for search results
$flightBooking = null;
$hotelBooking = null;
$passengers = [];
$septemberFlightHotel = [];
$flightsBySSN = [];

if ($flightBookingId) {
    $flightBooking = getFlightBookingInfo($flightBookingId);
}

if ($hotelBookingId) {
    $hotelBooking = getHotelBookingInfo($hotelBookingId);
}

if ($flightBookingId) {
    $passengers = getPassengersForFlight($flightBookingId);
}

if ($septemberBookings) {
    $septemberFlightHotel = getBookingsForSeptember2024();
}

if ($ssn) {
    $flightsBySSN = getFlightsBySSN($ssn);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mystyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Assignment3</h1>
        <p>Hi, <?php echo $user['first_name'] . ' ' . $user['last_name']; ?>!</p>
    </header>
    
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="stays.php">Stays</a></li>
            <li><a href="flights.php">Flights</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="admin-account.php">Account</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <aside>
            <h1 id="time"></h1>
            <p id="date"></p><br>
            <label for="font-size">Choose Font Size:</label>
            <select id="font-size">
                <option value="16px">Default</option>
                <option value="18px">18px</option>
                <option value="20px">20px</option>
                <option value="22px">22px</option>
                <option value="24px">24px</option>
            </select><br><br>
            <label for="bg-color">Choose Background:</label>
            <select id="bg-color">
                <option value="">Default</option>
                <option value="green">Green</option>
                <option value="#AA336A">Pink</option>
                <option value="#38B0DE">Blue</option>
                <option value="orange">Orange</option>
            </select>
            <script src="script.js"></script>
        </aside>
        
        <main>
            <h2>Account</h2><br>
            
            <!-- Form to retrieve flight and hotel details -->
            <h3>Retrieve Flight and Hotel Information</h3>
            <form method="POST">
                <label for="flight_booking_id">Enter Flight Booking ID:</label>
                <input type="text" id="flight_booking_id" name="flight_booking_id" value="<?php echo $flightBookingId; ?>">
                <button type="submit">Search Flight</button>
            </form>

            <?php if ($flightBooking): ?>
                <h4>Flight Booking Details</h4>
                <p>Flight ID: <?php echo $flightBooking['flight_booking_id']; ?></p>
                <p>Flight Price: $<?php echo $flightBooking['total_price']; ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="hotel_booking_id">Enter Hotel Booking ID:</label>
                <input type="text" id="hotel_booking_id" name="hotel_booking_id" value="<?php echo $hotelBookingId; ?>">
                <button type="submit">Search Hotel</button>
            </form>

            <?php if ($hotelBooking): ?>
                <h4>Hotel Booking Details</h4>
                <p>Hotel ID: <?php echo $hotelBooking['hotel_booking_id']; ?></p>
                <p>Total Price: $<?php echo $hotelBooking['total_price']; ?></p>
            <?php endif; ?>

            <!-- Display passengers for flight -->
            <?php if ($flightBookingId && count($passengers) > 0): ?>
                <h4>Passengers for Flight</h4>
                <?php foreach ($passengers as $passenger): ?>
                    <p>Name: <?php echo $passenger['first_name'] . ' ' . $passenger['last_name']; ?></p>
                    <p>SSN: <?php echo $passenger['ssn']; ?></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Form to retrieve September bookings -->
            <form method="POST">
                <button type="submit" name="september_bookings">Retrieve Bookings for September 2024</button>
            </form>

            <?php if ($septemberBookings && count($septemberFlightHotel) > 0): ?>
                <h4>Bookings for September 2024</h4>
                <?php foreach ($septemberFlightHotel as $booking): ?>
                    <p>Booking ID: <?php echo $booking['flight_booking_id'] ?? $booking['hotel_booking_id']; ?></p>
                    <p>Booking Type: <?php echo isset($booking['flight_booking_id']) ? 'Flight' : 'Hotel'; ?></p>
                    <p>Price: $<?php echo $booking['total_price']; ?></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Form to search flights by SSN -->
            <form method="POST">
                <label for="ssn">Enter SSN to search flights:</label>
                <input type="text" id="ssn" name="ssn" value="<?php echo $ssn; ?>">
                <button type="submit">Search Flights by SSN</button>
            </form>

            <?php if (count($flightsBySSN) > 0): ?>
                <h4>Flights for SSN: <?php echo $ssn; ?></h4>
                <?php foreach ($flightsBySSN as $flight): ?>
                    <p>Flight ID: <?php echo $flight['flight_booking_id']; ?></p>
                    <p>Price: $<?php echo $flight['total_price']; ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
    
    <footer>
        <h3>Om Hirpara: OMH200000</h3>
        <h3>Rohit Ramarathinam: RXR200060</h3>
        <h3>Pramith Prasanna: PXP200035</h3>
    </footer>
</body>
</html>
