<?php
session_start();  // Start the session

$is_logged_in = isset($_SESSION['first_name']);

if (!$is_logged_in) {
    header("Location: login.html");
    exit;
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
?>

<!DOCTYPE html>
<head>
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
            <!-- <li><a href="register.html">Register</a></li>
            <li><a href="login.html">Login</a></li> -->
            <?php if (!$is_logged_in): ?>
                <li><a href="register.html">Register</a></li>
                <li><a href="login.html">Login</a></li>
            <?php else: ?>
                <!-- Optionally, display a logout link or welcome message -->
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
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
            <h2>Stays</h2><br>
            <form id="stays-form">
                <!-- <label for="city-name">Select Destination</label> -->
                <select id="city-name">
                    <option value="---" selected>Select City</option>
                    <option value="Dallas, TX">Dallas, TX</option>
                    <option value="Austin, TX">Austin, TX</option>
                    <option value="Houston, TX">Houston, TX</option>
                    <option value="San Francisco, CA">San Francisco, CA</option>
                    <option value="Los Angeles, CA">Los Angeles, CA</option>
                    <option value="San Diego, CA">San Diego, CA</option>
                </select><br><br>
                <input type="text" id="check-in" name="check-in" placeholder="Check-In Date:" required><br><br>
                <input type="text" id="check-out" name="check-out" placeholder="Check-Out Date:" required><br><br>
                <br><h4>Guests: </h4>
                <label for="adults">Adults:</label>
                <input type="number" id="adults" name="adults" min="1" max="10" value="1"><br>
                <label for="adults">Children:</label>
                <input type="number" id="children" name="children" min="0" max="10" value="0"><br>
                <label for="infants">Infants:</label>
                <input type="number" id="infants" name="infants" min="0" max="10" value="0"><br><br>
                <input type="submit" value="Search"></button>
            </form><br><br>
            <div id="display-details"></div>
        </main>
    </div>

    <footer>
        <h3>Om Hirpara: OMH200000</h3>
        <h3>Rohit Ramarathinam: RXR200060</h3>
        <h3>Pramith Prasanna: PXP200035</h3>
    </footer>
</body>

<script>

    function searchHotels(checkIn, checkOut, rooms, passengers) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "hotels-info.json", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const hotelsData = JSON.parse(xhr.responseText);
                const hotels = hotelsData.hotels

                const city = document.getElementById('city-name').value;
                const cityHotels = hotels.filter(hotel => hotel.city === city && hotel['available-rooms'] >= rooms);
                const display = document.getElementById('display-details');
                display.innerHTML = '';
                if (cityHotels.length > 0) {
                    let ctr = 1;
                    cityHotels.forEach(hotel => {
                        const hotelDiv = document.createElement('div');
                        hotelDiv.classList.add("hotel");
                        hotelDiv.innerHTML = `
                            <p>ID: ${hotel['hotel-id']}</p>
                            <p>Name: ${hotel['hotel-name']}</p>
                            <p>City: ${hotel['city']}</p>
                            <p>Check-In Date: ${checkIn}</p>
                            <p>Check-Out Date: ${checkOut}</p>
                            <p>Rooms: ${rooms}</p>
                            <p>Price per Night: $${hotel['price-per-night']}</p><br>
                            <button class="add-to-cart-${ctr}">Add to Cart</button><br><br>
                        `;
                        display.appendChild(hotelDiv);
                        document.querySelector(`.add-to-cart-${ctr}`).addEventListener('click', () => addToCart(hotel, rooms, checkIn, checkOut, passengers));
                        ctr++;
                    });
                } else {
                    display.innerHTML = '<p>No hotels found for selected city.</p>';
                }
            };
        }
        xhr.send();
    }

    function addToCart(hotel, rooms, checkIn, checkOut, passengers) {
        const hotelData = {
            ...hotel,
            rooms: rooms,
            checkIn: checkIn,
            checkOut: checkOut,
            adults: passengers[0],
            children: passengers[1],
            infants: passengers[2]
        };
        sessionStorage.setItem("selectedHotel", JSON.stringify(hotelData));
        alert(`${hotel['hotel-name']} has been added to the cart.`)
    }

    function isDateValid(date) {
        const min = new Date('2024-09-01');
        const max = new Date('2024-12-01');
        const input = new Date(date);

        if (input.toISOString().substring(0, 10) !== date) {
            return false;
        }
        return input >= min && input <= max;
    }

    function validReturn(_dep, _ret) {
        const dep = new Date(_dep);
        const ret = new Date(_ret);
        return ret > dep;
    }

    document.getElementById('stays-form').addEventListener('submit', function(event) {

        const city = document.getElementById('city-name').value;
        const adults = Number(document.getElementById('adults').value);
        const children = Number(document.getElementById('children').value);
        const infants = Number(document.getElementById('infants').value);
        const checkIn = document.getElementById('check-in').value;
        const checkOut = document.getElementById('check-out').value;

        let rooms = Math.ceil((adults+children)/2);
        let guests = adults + children + infants;

        if (city === "---") {
            alert("Please select a city");
            event.preventDefault();
            return; 
        }

        if (!isDateValid(checkIn) || !isDateValid(checkOut)) {
            alert("Please enter a valid date between 2024-09-01 and 2024-12-01");
            event.preventDefault();
            return;
        }

        if (!validReturn(checkIn, checkOut)) {
            alert("Please enter a date after your check-in date");
            event.preventDefault();
            return;
        }

        else {
            let passengers = [adults, children, infants]
            searchHotels(checkIn, checkOut, rooms, passengers);
            event.preventDefault();
            return;
        }

/*         else {
            document.getElementById('display-details').innerHTML = `
                    <h4> Booked! Here are your details: </h4>
                    <p>City: ${city}</p>
                    <p>Total Guests: ${guests}</p>
                    <p>Rooms: ${rooms}</p>
                    <p>Check-In Date: ${checkIn}</p>
                    <p>Check-Out Date: ${checkOut}</p><br>
                    <h4> See you soon! </h4>
                `;
            event.preventDefault();
        } */

    })
</script>