<?php
session_start();

$is_logged_in = isset($_SESSION['first_name']);

if (!$is_logged_in) {
    header("Location: login.html");
    exit;
}

if ($_SESSION['phone-no'] !== "222-222-2222") {
    header("Location: user-account.php");
    exit;
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Database connection
include 'db.php';

// Function to fetch all booked flights from Texas in Sep 2024 - Oct 2024
function getFlightsFromTexas() {
    global $conn;

    // SQL query to join flights and flight_booking tables
    $sql = "SELECT f.flight_id, f.origin, f.destination, f.departure_date, f.arrival_date, f.departure_time, f.arrival_time, f.price, fb.flight_booking_id
            FROM flights f
            JOIN flight_booking fb ON f.flight_id = fb.flight_id
            WHERE f.origin IN ('Dallas, TX', 'Houston, TX', 'Austin, TX') 
            AND f.departure_date BETWEEN '2024-09-01' AND '2024-10-31'";

    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}


// Function to fetch all booked hotels from Texas in Sep 2024 - Oct 2024
function getHotelsInTexas() {
    global $conn;
    $sql = "SELECT * FROM hotel_booking hb
            JOIN hotels h ON hb.hotel_id = h.hotel_id
            WHERE h.city IN ('Dallas, TX', 'Houston, TX', 'Austin, TX')
            AND hb.check_in BETWEEN '2024-09-01' AND '2024-10-31'";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch most expensive booked hotels
function getMostExpensiveHotels() {
    global $conn;
    $sql = "SELECT * FROM hotel_booking hb
            JOIN hotels h ON hb.hotel_id = h.hotel_id
            ORDER BY hb.total_price DESC LIMIT 5";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch booked flights with an infant passenger
function getFlightsWithInfant() {
    global $conn;
    $sql = "SELECT DISTINCT f.flight_booking_id, f.flight_id
            FROM flight_booking f
            JOIN tickets t ON f.flight_booking_id = t.flight_booking_id
            JOIN passenger p ON t.ssn = p.ssn
            WHERE p.category = 'infants'";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch flights with an infant and at least 5 children
function getFlightsWithInfantAndChildren() {
    global $conn;
    $sql = "SELECT f.flight_booking_id, f.flight_id 
            FROM flight_booking f
            JOIN tickets t ON f.flight_booking_id = t.flight_booking_id
            JOIN passenger p ON t.ssn = p.ssn
            WHERE p.category = 'infants'
            GROUP BY f.flight_booking_id
            HAVING COUNT(CASE WHEN p.category = 'children' THEN 1 END) >= 5";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch most expensive booked flights
function getMostExpensiveFlights() {
    global $conn;
    $sql = "SELECT f.flight_booking_id, f.flight_id, f.total_price 
            FROM flight_booking f 
            ORDER BY f.total_price DESC LIMIT 5";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch flights from Texas with no infants
function getFlightsNoInfants() {
    global $conn;
    $sql = "SELECT DISTINCT f.flight_booking_id, f.flight_id 
            FROM flight_booking f
            JOIN flights fl ON f.flight_id = fl.flight_id
            JOIN tickets t ON f.flight_booking_id = t.flight_booking_id
            JOIN passenger p ON t.ssn = p.ssn
            WHERE p.category != 'infants' 
            AND fl.origin IN ('Dallas, TX', 'Houston, TX', 'Austin, TX')";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to count flights arriving in California in Sep 2024 - Oct 2024
function countFlightsToCalifornia() {
    global $conn;
    $sql = "SELECT COUNT(*) AS flight_count 
            FROM flight_booking f
            JOIN flights fl ON f.flight_id = fl.flight_id
            WHERE fl.destination IN ('Los Angeles, CA', 'San Francisco, CA', 'San Diego, CA')
            AND fl.arrival_date BETWEEN '2024-09-01' AND '2024-10-31'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['flight_count'];
}

$flightsFromTexas = getFlightsFromTexas();
$hotelsInTexas = getHotelsInTexas();
$mostExpensiveHotels = getMostExpensiveHotels();
$flightsWithInfant = getFlightsWithInfant();
$flightsWithInfantAndChildren = getFlightsWithInfantAndChildren();
$mostExpensiveFlights = getMostExpensiveFlights();
$flightsNoInfants = getFlightsNoInfants();
$flightsToCalifornia = countFlightsToCalifornia();
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
        <h1>Assignment4</h1>
        <p>Hi, <?php echo $first_name . ' ' . $last_name; ?>!</p>
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
            <h2>My Account</h2>
                <br><h3>Load Databases</h3>
                <div class="flex">
                    <form action="load_flights.php" method="post">
                        <button type="submit">Load Flights Data</button>
                    </form>
                    <form action="load_hotels.php" method="post">
                        <button type="submit">Load Hotels Data</button>
                    </form>
                </div>

                <br><br>

            <h3>Admin Queries</h3>
            <button onclick="loadData('flightsFromTexas')">Load Flights from Texas (Sep 2024 - Oct 2024)</button>
            <button onclick="loadData('hotelsInTexas')">Load Hotels in Texas (Sep 2024 - Oct 2024)</button>
            <button onclick="loadData('mostExpensiveHotels')">Load Most Expensive Hotels</button>
            <button onclick="loadData('flightsWithInfant')">Load Flights with Infant Passengers</button>
            <button onclick="loadData('flightsWithInfantAndChildren')">Load Flights with Infant and 5+ Children</button>
            <button onclick="loadData('mostExpensiveFlights')">Load Most Expensive Flights</button>
            <button onclick="loadData('flightsNoInfants')">Load Flights from Texas without Infants</button>
            <button onclick="loadData('flightsToCalifornia')">Load Flights to California (Sep 2024 - Oct 2024)</button>

            <!-- Results Section (Initially Hidden) -->
            <div id="resultsContainer" style="display:none; margin-top: 20px;">

                <!-- Flights from Texas (Sep-Oct 2024) -->
                <div id="flightsFromTexasResults" style="display:none;">
                    <h3>Flights from Texas (Sep 2024 - Oct 2024)</h3>
                    <?php foreach ($flightsFromTexas as $flight): ?>
                        <p>Flight ID: <?php echo $flight['flight_id']; ?> | Departure Date: <?php echo $flight['departure_date']; ?> | Price: $<?php echo $flight['price']; ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- Hotels in Texas (Sep-Oct 2024) -->
                <div id="hotelsInTexasResults" style="display:none;">
                    <h3>Hotels in Texas (Sep 2024 - Oct 2024)</h3>
                    <?php foreach ($hotelsInTexas as $hotel): ?>
                        <p>Hotel Name: <?php echo $hotel['hotel_name']; ?> | Check-in: <?php echo $hotel['check_in']; ?> | Price per Night: $<?php echo $hotel['price_per_night']; ?> | Total Price: $<?php echo $hotel['total_price']; ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- Most Expensive Hotels -->
                <div id="mostExpensiveHotelsResults" style="display:none;">
                    <h3>Most Expensive Hotels</h3>
                    <?php foreach ($mostExpensiveHotels as $hotel): ?>
                        <p>Hotel Name: <?php echo $hotel['hotel_name']; ?> | Total Price: $<?php echo $hotel['total_price']; ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- Flights with Infant -->
                <div id="flightsWithInfantResults" style="display:none;">
                    <h3>Flights with Infant Passengers</h3>
                    <?php foreach ($flightsWithInfant as $flight): ?>
                        <p>Flight Booking ID: <?php echo $flight['flight_booking_id']; ?> | Flight ID: <?php echo $flight['flight_id']; ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- Flights with Infant and 5+ Children -->
                <div id="flightsWithInfantAndChildrenResults" style="display:none;">
                    <h3>Flights with Infant and 5+ Children</h3>
                    <?php foreach ($flightsWithInfantAndChildren as $flight): ?>
                        <p>Flight Booking ID: <?php echo $flight['flight_booking_id']; ?> | Flight ID: <?php echo $flight['flight_id']; ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- Most Expensive Flights -->
                <div id="mostExpensiveFlightsResults" style="display:none;">
                    <h3>Most Expensive Flights</h3>
                    <?php foreach ($mostExpensiveFlights as $flight): ?>
                        <p>Flight Booking ID: <?php echo $flight['flight_booking_id']; ?> | Flight ID: <?php echo $flight['flight_id']; ?> | Price: $<?php echo $flight['total_price']; ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- Flights from Texas without Infants -->
                <div id="flightsNoInfantsResults" style="display:none;">
                    <h3>Flights from Texas without Infants</h3>
                    <?php foreach ($flightsNoInfants as $flight): ?>
                        <p>Flight Booking ID: <?php echo $flight['flight_booking_id']; ?> | Flight ID: <?php echo $flight['flight_id']; ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- Flights to California (Sep 2024 - Oct 2024) -->
                <div id="flightsToCaliforniaResults" style="display:none;">
                    <h3>Number of Flights to California (Sep 2024 - Oct 2024)</h3>
                    <p>Total Flights: <?php echo $flightsToCalifornia; ?></p>
                </div>

            </div>

            <script>
                function loadData(section) {
                    // Hide all result sections
                    document.getElementById('resultsContainer').style.display = 'block'; // Make the results container visible
                    var sections = [
                        'flightsFromTexas',
                        'hotelsInTexas',
                        'mostExpensiveHotels',
                        'flightsWithInfant',
                        'flightsWithInfantAndChildren',
                        'mostExpensiveFlights',
                        'flightsNoInfants',
                        'flightsToCalifornia'
                    ];
                    sections.forEach(function (id) {
                        document.getElementById(id + 'Results').style.display = 'none';
                    });

                    // Show the selected result section
                    document.getElementById(section + 'Results').style.display = 'block';
                }
            </script>
        </main>

    </div>
    
    <footer>
        <h3>Om Hirpara: OMH200000</h3>
        <h3>Rohit Ramarathinam: RXR200060</h3>
        <h3>Pramith Prasanna: PXP200035</h3>
    </footer>
</body>
</html>
