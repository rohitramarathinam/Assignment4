<?php
session_start();

$is_logged_in = isset($_SESSION['first_name']);

if (!$is_logged_in) {
    header("Location: login.html");
    exit;
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
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
</head>
<body>
    <header>
        <h1>Assignment3</h1>
        <p>Hi, <?php echo $first_name . ' ' . $last_name; ?>!</p>
    </header>
    
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="stays.php">Stays</a></li>
            <li><a href="flights.php">Flights</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="account.php">Account</a></li>
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
            <h2>Cart</h2><br>
            <div class="flex">
                <div>
                    <input type="radio" id="htl" name="type" value="hotels">
                    <label for="htl">Hotels</label><br>
                </div>
                <div>
                    <input type="radio" id="flt" name="type" value="flights">
                    <label for="flt">Flights</label><br>
                </div>
            </div>
            <div id="display"></div>
        </main>
    </div>
    
    <footer>
        <h3>Om Hirpara: OMH200000</h3>
        <h3>Rohit Ramarathinam: RXR200060</h3>
        <h3>Pramith Prasanna: PXP200035</h3>
    </footer>
</body>

<script>
    function createHotelXML(hotel) {
        return `\t<booking>
        <hotel-id>${hotel['hotel-id']}</hotel-id>
        <hotel-name>${hotel['hotel-name']}</hotel-name>
        <city>${hotel['city']}</city>
        <check-in>${hotel['checkIn']}</check-in>
        <check-out>${hotel['checkOut']}</check-out>
        <guests>
            <adults>${hotel['adults']}</adults>
            <children>${hotel['children']}</children>
            <infants>${hotel['infants']}</infants>
        </guests>
        <rooms>${hotel['rooms']}</rooms>
        <price-per-night>${hotel['price-per-night']}</price-per-night>
        <total-price>${calculateHotelPrice(hotel['rooms'], hotel['price-per-night'], new Date(hotel['checkIn']), new Date(hotel['checkOut']))}</total-price>
    </booking>`;
    }

    function generateBookingUUID() {
        let part1 = String.fromCharCode(65 + Math.floor(Math.random() * 26)) + String.fromCharCode(65 + Math.floor(Math.random() * 26));
        let part2 = Array.from({length : 5}, () => Math.floor(Math.random()*10)).join("");
        return `${part1}-${part2}`;
    }

    function createFlightObject(flight, flight2, bid1, bid2, passengersObj) {
        let flightObject = {}
        if (flight2) {
            flightObject = {
                "trip-type": "round-trip",
                "departing": {
                    "booking-id": bid1,
                    "flight-id": flight["flight-id"],
                    "origin": flight["origin"],
                    "destination": flight["destination"],
                    "departure-date": flight["departure-date"],
                    "arrival-date": flight["arrival-date"],
                    "departure-time": flight["departure-time"],
                    "arrival-time": flight["arrival-time"]
                },
                "returning": {
                    "booking-id": bid2,
                    "flight-id": flight2["flight-id"],
                    "origin": flight2["origin"],
                    "destination": flight2["destination"],
                    "departure-date": flight2["departure-date"],
                    "arrival-date": flight2["arrival-date"],
                    "departure-time": flight2["departure-time"],
                    "arrival-time": flight2["arrival-time"]
                },
                "passengers": passengersObj
            };
        } else {
            flightObject = {
                "trip-type": "one-way",
                "departing": {
                    "booking-id": bid1,
                    "flight-id": flight["flight-id"],
                    "origin": flight["origin"],
                    "destination": flight["destination"],
                    "departure-date": flight["departure-date"],
                    "arrival-date": flight["arrival-date"],
                    "departure-time": flight["departure-time"],
                    "arrival-time": flight["arrival-time"]
                },
                "passengers": passengersObj
            };
        }
        return flightObject;
    }

    function displayHotel() {
        const display = document.getElementById("display");
        display.innerHTML = ``;
        const hotel = JSON.parse(sessionStorage.getItem("selectedHotel"));
        
        if (hotel) {
            const hotelDiv = document.createElement('div');
            const totalPrice = calculateHotelPrice(hotel['rooms'], hotel['price-per-night'], new Date(hotel['checkIn']), new Date(hotel['checkOut']));
            hotelDiv.classList.add("hotel");
            hotelDiv.innerHTML = `
                <p>ID: ${hotel['hotel-id']}</p>
                <p>Name: ${hotel['hotel-name']}</p>
                <p>City: ${hotel['city']}</p>
                <p>Check-In Date: ${hotel['checkIn']}</p>
                <p>Check-Out Date: ${hotel['checkOut']}</p>
                <p>Number of Rooms: ${hotel['rooms']}</p>
                <p>Adults: ${hotel['adults']}</p>
                <p>Children: ${hotel['children']}</p>
                <p>Infants: ${hotel['infants']}</p>
                <p>Price per Night: $${hotel['price-per-night']}</p>
                <p>Total Price: $${totalPrice}</p><br>
                <div class="flex">
                    <input id="book-hotel" type="submit" value="Book"></button>
                    <input id="clear-hotel" type="submit" value="Clear"></button>
                </div>
            `;
            display.appendChild(hotelDiv);

            document.getElementById("book-hotel").addEventListener("click", function() {
                event.preventDefault();
                let xmlString = createHotelXML(hotel);
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "/book-hotels", true);
                xhr.setRequestHeader("Content-Type", "application/xml");
                xhr.onload = function() {};
                xhr.onerror = function() {
                    alert("Unable to save your hotel details.");
                };
                xhr.send(xmlString);
                hotelDiv.innerHTML = ``;
                hotelDiv.innerHTML = `<p>Your booking is confirmed.</p><br>`;
                hotelDiv.innerHTML += `
                    <p>ID: ${hotel['hotel-id']}</p>
                    <p>Name: ${hotel['hotel-name']}</p>
                    <p>City: ${hotel['city']}</p>
                    <p>Check-In Date: ${hotel['checkIn']}</p>
                    <p>Check-Out Date: ${hotel['checkOut']}</p>
                    <p>Number of Rooms: ${hotel['rooms']}</p>
                    <p>Adults: ${hotel['adults']}</p>
                    <p>Children: ${hotel['children']}</p>
                    <p>Infants: ${hotel['infants']}</p>
                    <p>Price per Night: $${hotel['price-per-night']}</p>
                    <p>Total Price: $${totalPrice}</p><br>
                `;
                clearChoice("selectedHotel");
            });

            document.getElementById("clear-hotel").addEventListener("click", function() {
                clearChoice("selectedHotel");
                hotelDiv.innerHTML = ``;
                hotelDiv.innerHTML = `Your hotel booking has been cleared.`;
            });
        } else {
            display.innerHTML = `<p>No hotels have been added to the cart.</p>`;
        }
    }

    function calculateHotelPrice(rooms, ppn, checkin, checkout) {
        let nights = (checkout - checkin) / (1000 * 60 * 60 * 24);
        return nights * ppn * rooms;
    }

    function calculateFlightPrice(price1, price2, adults, children, infants) {
        return (0.1 * price1 * infants) + (0.7 * price1 * children) + (price1 * adults) + (0.1 * price2 * infants) + (0.7 * price2 * children) + (price2 * adults);
    }

    function displayFlight() {
        const display = document.getElementById("display");
        display.innerHTML = ``;
        const flight = JSON.parse(sessionStorage.getItem("selectedFlight"));
        const flight2 = JSON.parse(sessionStorage.getItem("selectedReturnFlight"));

        if (flight) {
            const flightDiv = document.createElement('div');
            flightDiv.classList.add("flight");

            if (flight2) {
                flightDiv.innerHTML = `
                    <h4>Departing</h4><br>
                    <div>
                        <p>Flight ID: ${flight["flight-id"]}</p>
                        <p>Origin: ${flight["origin"]}</p>
                        <p>Destination: ${flight["destination"]}</p>
                        <p>Departure Date: ${flight["departure-date"]}</p>
                        <p>Arrival Date: ${flight["arrival-date"]}</p>
                        <p>Departure Time: ${flight["departure-time"]}</p>
                        <p>Arrival Time: ${flight["arrival-time"]}</p>
                    </div><br><br>
                    <h4>Returning</h4><br>
                    <div>
                        <p>Flight ID: ${flight2["flight-id"]}</p>
                        <p>Origin: ${flight2["origin"]}</p>
                        <p>Destination: ${flight2["destination"]}</p>
                        <p>Departure Date: ${flight2["departure-date"]}</p>
                        <p>Arrival Date: ${flight2["arrival-date"]}</p>
                        <p>Departure Time: ${flight2["departure-time"]}</p>
                        <p>Arrival Time: ${flight2["arrival-time"]}</p>
                    </div><br><br>
                    <h4>Total Price: $${calculateFlightPrice(Number(flight["price"]), Number(flight2["price"]), Number(flight["adults"]), Number(flight["children"]), Number(flight["infants"]))}</h4><br><br>
                    <div class="flex">
                        <input id="enter-passengers" type="submit" value="Next"></button>
                        <input id="clear-flight" type="submit" value="Clear"></button>
                    </div>
                `;
            } else {
                flightDiv.innerHTML = `
                    <h4>Departing</h4><br>
                    <p>Flight ID: ${flight["flight-id"]}</p>
                    <p>Origin: ${flight["origin"]}</p>
                    <p>Destination: ${flight["destination"]}</p>
                    <p>Departure Date: ${flight["departure-date"]}</p>
                    <p>Arrival Date: ${flight["arrival-date"]}</p>
                    <p>Departure Time: ${flight["departure-time"]}</p>
                    <p>Arrival Time: ${flight["arrival-time"]}</p><br><br>
                    <h4>Total Price: $${calculateFlightPrice(Number(flight["price"]), 0, Number(flight["adults"]), Number(flight["children"]), Number(flight["infants"]))}</h4><br><br>
                    <div class="flex">
                        <input id="enter-passengers" type="submit" value="Next"></button>
                        <input id="clear-flight" type="submit" value="Clear"></button>
                    </div>
                `;
            }

            display.appendChild(flightDiv);

            document.getElementById("clear-flight").addEventListener("click", function() {
                clearChoice("selectedFlight");
                if (flight2) {
                    clearChoice("selectedFlight2");
                }
                flightDiv.innerHTML = ``;
                flightDiv.innerHTML = `Your flight booking has been cleared.`;
            });

            document.getElementById("enter-passengers").addEventListener("click", function() {
                flightDiv.innerHTML = ``;
                flightDiv.innerHTML = `
                    <br><h3>Passengers</h3><br>
                    <div id="passenger-forms" class="flex"></div>
                    <input id="book-flight" type="submit" value="Book"></button>
                `;

                display.appendChild(flightDiv);

                const passengers = Number(flight["total-passengers"]);
                let ctr = 1;
                
                while (ctr <= passengers) {
                    const passengerForm = document.createElement('div');
                    passengerForm.classList.add("passenger-form");
                    passengerForm.innerHTML = `
                        <h4>Passenger ${ctr}</h4>
                        <input type="text" id="ssn-${ctr}" name="ssn" placeholder="SSN" required><br><br>
                        <input type="text" id="first-name-${ctr}" name="first-name" placeholder="First Name" required><br><br>
                        <input type="text" id="last-name-${ctr}" name="last-name" placeholder="Last Name" required><br><br>
                        <input type="text" id="dob-${ctr}" name="dob" placeholder="Date of Birth" required><br><br>
                    `;
                    document.getElementById("passenger-forms").appendChild(passengerForm);
                    ctr++;
                }

                document.getElementById("book-flight").addEventListener("click", function() {
                    event.preventDefault();
                    let ctr = 1;
                    let passengersObj = [];
                    while (ctr <= passengers) {
                        let ssn = document.getElementById(`ssn-${ctr}`).value;
                        let fname = document.getElementById(`first-name-${ctr}`).value;
                        let lname = document.getElementById(`last-name-${ctr}`).value;
                        let dob = document.getElementById(`dob-${ctr}`).value;

                        if (validateUserInputs(ssn, fname, lname, dob) === false) {
                            return;
                        }

                        const passenger = {
                            "SSN": document.getElementById(`ssn-${ctr}`).value,
                            "first-name": document.getElementById(`first-name-${ctr}`).value,
                            "last-name": document.getElementById(`last-name-${ctr}`).value,
                            "date-of-birth": document.getElementById(`dob-${ctr}`).value
                        };
                        passengersObj.push(passenger);
                        ctr++;
                    }
                    let bid1 = generateBookingUUID();
                    let bid2 = generateBookingUUID();
                    let jsonObject = (flight2) ? createFlightObject(flight, flight2, bid1, bid2, passengersObj) : createFlightObject(flight, null, bid1, null, passengersObj);
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "/book-flights", true);
                    xhr.setRequestHeader("Content-Type", "application/json");
                    xhr.onload = function() {};
                    xhr.onerror = function() {
                        alert("Unable to save your flight details.");
                    };
                    xhr.send(JSON.stringify(jsonObject));
                    flightDiv.innerHTML = ``;
                    flightDiv.innerHTML = `<h4>Your booking is confirmed.</h4><br>`;
                    if (flight2) {
                        flightDiv.innerHTML += `
                            <h4>Departing</h4><br>
                            <div>
                                <p>Booking ID: ${bid1}<p>
                                <p>Flight ID: ${flight["flight-id"]}</p>
                                <p>Origin: ${flight["origin"]}</p>
                                <p>Destination: ${flight["destination"]}</p>
                                <p>Departure Date: ${flight["departure-date"]}</p>
                                <p>Arrival Date: ${flight["arrival-date"]}</p>
                                <p>Departure Time: ${flight["departure-time"]}</p>
                                <p>Arrival Time: ${flight["arrival-time"]}</p>
                            </div><br><br>
                            <h4>Returning</h4><br>
                            <div>
                                <p>Booking ID: ${bid2}<p>
                                <p>Flight ID: ${flight2["flight-id"]}</p>
                                <p>Origin: ${flight2["origin"]}</p>
                                <p>Destination: ${flight2["destination"]}</p>
                                <p>Departure Date: ${flight2["departure-date"]}</p>
                                <p>Arrival Date: ${flight2["arrival-date"]}</p>
                                <p>Departure Time: ${flight2["departure-time"]}</p>
                                <p>Arrival Time: ${flight2["arrival-time"]}</p>
                            </div><br><br>
                        `;
                    } else {
                        flightDiv.innerHTML += `
                            <h4>Departing</h4><br>
                            <div>
                                <p>Booking ID: ${bid1}<p>
                                <p>Flight ID: ${flight["flight-id"]}</p>
                                <p>Origin: ${flight["origin"]}</p>
                                <p>Destination: ${flight["destination"]}</p>
                                <p>Departure Date: ${flight["departure-date"]}</p>
                                <p>Arrival Date: ${flight["arrival-date"]}</p>
                                <p>Departure Time: ${flight["departure-time"]}</p>
                                <p>Arrival Time: ${flight["arrival-time"]}</p>
                            </div><br><br>
                        `;
                    }
                    flightDiv.innerHTML += `<h4>Passengers</h4><br>`;
                    passengersObj.forEach(passenger => {
                        const passengerDiv = document.createElement('div');
                        passengerDiv.classList.add("passenger");
                        passengerDiv.innerHTML = `
                            <p>First Name: ${passenger['first-name']}</p>
                            <p>Last Name: ${passenger['last-name']}</p>
                            <p>SSN: ${passenger['SSN']}</p>
                            <p>Date of Birth: ${passenger['date-of-birth']}</p><br>
                        `;
                        flightDiv.appendChild(passengerDiv);
                    });
                    clearChoice("selectedFlight");
                });
            });
        } else {
            display.innerHTML = `<p>No flights have been added to the cart.</p>`;
        }
    }

    function validateUserInputs(ssn, fname, lname, dob) {
        const ssnRegex = /^\d{3}-\d{2}-\d{4}$/;
        const nameRegex = /^[a-zA-Z]+$/;
        const dobRegex = /^\d{4}-\d{2}-\d{2}$/; 

        if (!ssnRegex.test(ssn)) {
            alert("SSN must be in the format xxx-xx-xxxx.");
            return false;
        }

        if (!nameRegex.test(fname)) {
            alert("First name should only consist of letters.");
            return false;
        }

        if (!nameRegex.test(lname)) {
            alert("Last name should only consist of letters.");
            return false;
        }

        if (!dobRegex.test(dob)) {
            alert("Please enter a date of birth in the format yyyy-mm-dd.");
            return false;
        }

        const bdate = new Date(dob);
        if (isNaN(bdate.getTime())) {
            alert("Invalid date entered. Please enter a valid date.");
            return false;
        }

        return true;
    }

    function clearChoice(sessionId) {
        sessionStorage.clear(sessionId);
    }
    
    document.getElementById("htl").addEventListener("click", displayHotel);
    document.getElementById("flt").addEventListener("click", displayFlight);

</script>
</html>