<?php
session_start();
include 'db.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
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

        <main id="main-content">
            <h2>Flights</h2><br>
            <form id="flights-form">
                <select id="type-trip">
                    <option value="---" selected>---</option>
                    <option value="one-way">One-Way</option>
                    <option value="round-trip">Round Trip</option>
                </select><br><br>
                <!-- <div class="flex">
                    <input type="text" id="origin" name="origin" placeholder="Origin" required><br><br>
                    <input type="text" class="short" id="origin-state" name="origin-state" placeholder="State" required><br><br>
                </div> -->
                <select id="origin-city">
                    <option value="---" selected>Origin</option>
                    <option value="Dallas, TX">Dallas, TX</option>
                    <option value="Austin, TX">Austin, TX</option>
                    <option value="Houston, TX">Houston, TX</option>
                    <option value="San Francisco, CA">San Francisco, CA</option>
                    <option value="Los Angeles, CA">Los Angeles, CA</option>
                    <option value="San Diego, CA">San Diego, CA</option>
                </select><br><br>
                <!-- <div class="flex">
                    <input type="text" id="destination" name="destination" placeholder="Destination" required><br><br>
                    <input type="text" class="short" id="destination-state" name="destination-state" placeholder="State" required><br><br>
                </div> -->
                <select id="destination-city">
                    <option value="---" selected>Destination</option>
                    <option value="Dallas, TX">Dallas, TX</option>
                    <option value="Austin, TX">Austin, TX</option>
                    <option value="Houston, TX">Houston, TX</option>
                    <option value="San Francisco, CA">San Francisco, CA</option>
                    <option value="Los Angeles, CA">Los Angeles, CA</option>
                    <option value="San Diego, CA">San Diego, CA</option>
                </select><br><br>
                <div class="flex">
                    <input type="text" id="departure-date" name="departure-date" placeholder="Departure Date:" required><br><br>
                    <div id="return-field" class="hidden"><input type="text" id="return-date" name="return-date" placeholder="Return Date:"></div>
                </div>
                <i id="passenger-icon" class="fa-solid fa-person"></i><br><br>
                <div id="passenger-select" class="hidden">
                    <h4>Passengers: </h4>
                    <label for="adults">Adults: </label>
                    <input type="number" id="adults" name="adults" min="0" value="1"><br>
                    <label for="children">Children: </label>
                    <input type="number" id="children" name="children" min="0" value="0"><br>
                    <label for="infants">Infants: </label>
                    <input type="number" id="infants" name="infants" min="0" value="0"><br><br>
                </div>
                <input type="submit" value="Search">
            </form><br><br>
            <div id="displays" class="flex2">
                <div id="display-details"></div>
                <div id="display-details-returning"></div>
            </div>
        </main>
    </div>

    <footer>
        <h3>Om Hirpara: OMH200000</h3>
        <h3>Rohit Ramarathinam: RXR200060</h3>
        <h3>Pramith Prasanna: PXP200035</h3>
    </footer>
</body>

<script>

    let outboundFlights = [];
    function searchFlights() {
        const tripType = document.getElementById('type-trip').value;
        const origin = document.getElementById('origin-city').value;
        const destination = document.getElementById('destination-city').value;
        const departureDate = document.getElementById('departure-date').value;
        const returnDate = document.getElementById('return-date').value;
        const adults = Number(document.getElementById('adults').value);
        const children = Number(document.getElementById('children').value)
        const infants = Number(document.getElementById('infants').value);
        const totalPassengers = adults + children + infants;
        const display = document.getElementById('display-details');
        const displayRet = document.getElementById('display-details-returning');

        // Clear previous search results
        display.innerHTML = '';

        const xhr = new XMLHttpRequest();
        xhr.open("GET", "flights-info.xml", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const xmlDoc = xhr.responseXML;
                const flights = Array.from(xmlDoc.getElementsByTagName("flight"));
                
                let outboundFlights = flights.filter(flight => {
                    const flightOrigin = flight.getElementsByTagName("origin")[0].textContent;
                    const flightDestination = flight.getElementsByTagName("destination")[0].textContent;
                    const flightDate = flight.getElementsByTagName("departure-date")[0].textContent;
                    const availableSeats = parseInt(flight.getElementsByTagName("available-seats")[0].textContent);

                    return flightOrigin === origin && 
                        flightDestination === destination && 
                        flightDate === departureDate && 
                        availableSeats >= totalPassengers;
                });

                let returnFlights = [];
                if (tripType === "round-trip") {
                    returnFlights = flights.filter(flight => {
                        const flightOrigin = flight.getElementsByTagName("origin")[0].textContent;
                        const flightDestination = flight.getElementsByTagName("destination")[0].textContent;
                        const flightDate = flight.getElementsByTagName("departure-date")[0].textContent;
                        const availableSeats = parseInt(flight.getElementsByTagName("available-seats")[0].textContent);

                        return flightOrigin === destination && 
                            flightDestination === origin && 
                            flightDate === returnDate && 
                            availableSeats >= totalPassengers;
                    });
                }

                if (outboundFlights.length === 0) {
                    const targetDate = new Date(departureDate);

                    outboundFlights = flights.filter(flight => {
                        const flightOrigin = flight.getElementsByTagName("origin")[0].textContent;
                        const flightDestination = flight.getElementsByTagName("destination")[0].textContent;
                        const flightDate = flight.getElementsByTagName("departure-date")[0].textContent;
                        const availableSeats = parseInt(flight.getElementsByTagName("available-seats")[0].textContent);

                        const flightDateObj = new Date(flightDate);
                        const dateDifference = Math.abs((flightDateObj - targetDate) / (1000 * 60 * 60 * 24));

                        return flightOrigin === origin && 
                            flightDestination === destination && 
                            dateDifference <= 3 && 
                            availableSeats >= totalPassengers;
                    });
                }

                if (tripType === "round-trip" && returnFlights.length === 0) {
                    const targetDate = new Date(returnDate);

                    returnFlights = flights.filter(flight => {
                        const flightOrigin = flight.getElementsByTagName("origin")[0].textContent;
                        const flightDestination = flight.getElementsByTagName("destination")[0].textContent;
                        const flightDate = flight.getElementsByTagName("departure-date")[0].textContent;
                        const availableSeats = parseInt(flight.getElementsByTagName("available-seats")[0].textContent);

                        const flightDateObj = new Date(flightDate);
                        const dateDifference = Math.abs((flightDateObj - targetDate) / (1000 * 60 * 60 * 24));

                        return flightOrigin === destination && 
                            flightDestination === origin && 
                            dateDifference <= 3 && 
                            availableSeats >= totalPassengers;
                    });
                }

                if (outboundFlights.length > 0) {
                    display.innerHTML = ``;
                    displayRet.innerHTML = ``;
                    display.innerHTML = `<h4>Departing</h4><br>`;
                    outboundFlights.forEach(flight => displayFlight(flight, false));
                    if (tripType === "round-trip" && returnFlights.length > 0) {
                        displayRet.innerHTML += `<h4>Returning</h4><br>`;
                        returnFlights.forEach(flight => displayFlight(flight, true));
                    } else if (tripType === "round-trip") {
                        displayRet.innerHTML = `<p>No return flights found on or within 3 days of selected return date.`;
                    }
                } else {
                    display.innerHTML = `<p>No flights found on or within 3 days of selected departure date.`;
                    displayRet.innerHTML = ``;
                }
            }
        };

        xhr.send();
    }

    function displayFlight(flight, returning) {
        const flightId = flight.getElementsByTagName("flight-id")[0].textContent;
        const flightOrigin = flight.getElementsByTagName("origin")[0].textContent;
        const flightDestination = flight.getElementsByTagName("destination")[0].textContent;
        const flightDate = flight.getElementsByTagName("departure-date")[0].textContent;
        const arrivalDate = flight.getElementsByTagName("arrival-date")[0].textContent;
        const departureTime = flight.getElementsByTagName("departure-time")[0].textContent;
        const arrivalTime = flight.getElementsByTagName("arrival-time")[0].textContent;
        const availableSeats = flight.getElementsByTagName("available-seats")[0].textContent;
        const price = flight.getElementsByTagName("price")[0].textContent;
        const adults = Number(document.getElementById('adults').value);
        const children = Number(document.getElementById('children').value)
        const infants = Number(document.getElementById('infants').value);
        const totalPassengers = adults + children + infants;
        const display = (returning) ? document.getElementById('display-details-returning') : document.getElementById('display-details');

        const flightDiv = document.createElement('div');
        flightDiv.classList.add("flight");
        flightDiv.innerHTML = `
            <p>Flight ID: ${flightId}</p>
            <p>Origin: ${flightOrigin}</p>
            <p>Destination: ${flightDestination}</p>
            <p>Departure Date: ${flightDate}</p>
            <p>Arrival Date: ${arrivalDate}</p>
            <p>Departure Time: ${departureTime}</p>
            <p>Arrival Time: ${arrivalTime}</p>
            <p>Available Seats: ${availableSeats}</p>
            <p>Price: $${price}</p><br>
            <button class="add-to-cart">Add to Cart</button><br><br>
        `;
        display.appendChild(flightDiv);
        flightDiv.querySelector(".add-to-cart").addEventListener('click', () => addToCart(flight, adults, children, infants, totalPassengers, returning));
    }

    function addToCart(flight, adults, children, infants, totalPassengers, returning) {
        const flightDetails = flight.textContent.trim().split("\n");
        const flightObject = {
            "flight-id": flightDetails[0].trim(),
            "origin": flightDetails[1].trim(),
            "destination": flightDetails[2].trim(),
            "departure-date": flightDetails[3].trim(),
            "departure-time": flightDetails[4].trim(),
            "arrival-date": flightDetails[5].trim(),
            "arrival-time": flightDetails[6].trim(),
            "available-seats": flightDetails[7].trim(),
            "price": flightDetails[8].trim(),
            "adults": adults,
            "children": children,
            "infants": infants,
            "total-passengers": totalPassengers
        };
        
        if (returning) {
            sessionStorage.setItem("selectedReturnFlight", JSON.stringify(flightObject));
        } else {
            sessionStorage.setItem("selectedFlight", JSON.stringify(flightObject));
        }

        alert(`${flightObject["flight-id"]} has been added to the cart.`);
        console.log(flightObject);
    }



    const tripSelect = document.getElementById('type-trip');
    const returnField = document.getElementById('return-field');

    function toggleField() {
        const trip = tripSelect.value;
        if (trip==="round-trip") {
            returnField.classList.remove('hidden')
        }
        else {
            returnField.classList.add('hidden')
        }
    }

    function toggleForm() {
        const formStatus = document.getElementById('passenger-select');
        if (formStatus.classList.contains('hidden')) {
            formStatus.classList.remove('hidden');
        } 
        else {
            formStatus.classList.add('hidden');
        }
    }

    window.onload = function() {
        toggleField();
    }

    document.getElementById('passenger-icon').addEventListener('click', toggleForm);

    document.getElementById('type-trip').addEventListener('change', toggleField);

    document.getElementById('flights-form').addEventListener('submit', function(event) {

        // const origin = document.getElementById('origin').value;
        // const originState = document.getElementById('origin-state').value;
        // const destination = document.getElementById('destination').value;
        // const destinationState = document.getElementById('destination-state').value;
        const trip = tripSelect.value;
        const departureDate = document.getElementById('departure-date').value;
        const returnDate = document.getElementById('return-date').value;
        const adults = Number(document.getElementById('adults').value);
        const children = Number(document.getElementById('children').value);
        const infants = Number(document.getElementById('infants').value);
        let passengers = adults + children + infants;

        const dateRegex = /2024-((09|11)-(0[1-9]|1\d|2\d|30))|(10-(0[1-9]|1\d|2\d|3[0|1])|(12-01))$/;
        const adultRegex = /^[1-4]$/;
        const underageRegex = /^[0-4]$/;
        const stateRegex = /^(tx|ca)$/i;

        // if (!stateRegex.test(originState)) {
        //     alert("Please enter one of TX or CA for origin state.");
        //     event.preventDefault();
        //     return;
        // }

        // if (!stateRegex.test(destinationState)) {
        //     alert("Please enter one of TX or CA for destination state.");
        //     event.preventDefault();
        //     return;
        // }

        if (!dateRegex.test(departureDate)) {
            alert("Please enter a valid date between 2024-09-01 and 2024-12-01 and in format yyyy-mm-dd");
            event.preventDefault();
            return;
        }

        if (trip==="round-trip") {
            if (!dateRegex.test(returnDate)) {
                alert("Please enter a valid date between 2024-09-01 and 2024-12-01 and in format yyyy-mm-dd");
                event.preventDefault();
                return;
            }

            const departure = new Date(departureDate);
            const returnD = new Date(returnDate);

            if (returnD <= departure) {
                alert("Return date must be after the departure date.");
                event.preventDefault();
                return;
            }
        }

        let valid_trip_types = ['one-way', 'round-trip'];
        if (!valid_trip_types.includes(trip)) {
            alert("Please select a trip type");
            event.preventDefault();
            return;
        }

        if (!adultRegex.test(adults)) {
            alert("Number of adults cannot be more than 4 and there must be at least 1 adult traveling");
            event.preventDefault();
            return;
        }

        if (!underageRegex.test(children)) {
            alert("Number of children cannot be more than 4");
            event.preventDefault();
            return;
        }

        if (!underageRegex.test(infants)) {
            alert("Number of infants cannot be more than 4");
            event.preventDefault();
            return;
        }

        else {
            searchFlights();
            event.preventDefault();
            return;
        }

        // else {
        //     if (trip==="round-trip") {
        //         document.getElementById('display-details').innerHTML = `
        //                 <h4> Booked! Here are your details: </h4>
        //                 <p>Origin: ${origin}</p>
        //                 <p>Destination: ${destination}</p>
        //                 <p>Trip Type: Round Trip</p>
        //                 <p>Departing: ${departureDate}</p>
        //                 <p>Returning: ${returnDate}</p>
        //                 <p>Total Passengers: ${passengers}</p>
        //                 <h4> See you soon! </h4>
        //             `;
        //     }
        //     else {
        //         document.getElementById('display-details').innerHTML = `
        //                 <h4> Booked! Here are your details: </h4>
        //                 <p>Origin: ${origin}</p>
        //                 <p>Destination: ${destination}</p>
        //                 <p>Trip Type: One Way</p>
        //                 <p>Departing: ${departureDate}</p>
        //                 <p>Total Passengers: ${passengers}</p><br>
        //                 <h4> See you soon! </h4>
        //             `;
        //     }
        //     event.preventDefault();
        // }

    })
</script>