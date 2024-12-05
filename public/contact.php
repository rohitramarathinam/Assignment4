<?php
session_start();

$is_logged_in = isset($_SESSION['first_name']);

if (!$is_logged_in) {
    header("Location: login.html");
    exit;
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['contactID'])) {
    // Handle JavaScript variable sent via AJAX
    $comment = $_POST['comment'];
    $contactID = $_POST['contactID'];
    echo "Received from JavaScript";
    exit; 
}
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

        <main>
            <h2>Contact Us</h2><br>
            <form id="contact-form">
                <!-- <input type="text" id="first-name" name="first-name" placeholder="First Name"><br><br>
                <input type="text" id="last-name" name="last-name" placeholder="Last Name"><br><br>
                <input type="text" id="phone-no" name="phone-no" placeholder="Phone"><br><br>
                <label for="gender"> Select your gender: </label><br>
                    <input type="radio" id="male" name="gender" value="male">
                    <label for="male">Male</label><br>
                    <input type="radio" id="female" name="gender" value="female">
                    <label for="female">Female</label><br>
                    <input type="radio" id="other" name="gender" value="other">
                    <label for="other">Other</label><br><br>
                <input type="text" id="email" name="email" placeholder="Email"><br><br> -->
                <textarea id="comment" name="comment" placeholder="Write your comment here" rows = 10 cols = 50></textarea><br><br>
                <input type="submit" value="Submit"></button>
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
    document.getElementById('contact-form').addEventListener('submit', function(event) {
            
        const nameRegex = /^[A-Z][a-zA-Z]+$/;
        const phoneRegex = /^\d{3}-\d{3}-\d{4}$/; 
        const emailRegex = /[a-zA-Z0-9]+\@[a-zA-Z]+\.[a-zA-Z]+$/;
        const commentRegex = /.{10}/;

        // const fname = document.getElementById('first-name').value;
        // const lname = document.getElementById('last-name').value;
        // const phoneNo = document.getElementById('phone-no').value;
        // const email = document.getElementById('email').value;
        const comment = document.getElementById('comment').value;
        const contactID = Math.floor(Math.random() * 1e6); // Generates a number between 0 and 999,999
        // Send JavaScript data to PHP using AJAX
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment=${encodeURIComponent(comment)}&contactID=${encodeURIComponent(contactID)}`
        })
        .then(response => response.text())
        .then(data => console.log(data));

        // need to add user DOB from database
        function createXML(comment, contactID) {

            <?php
            $filename = 'saved-contacts.xml';
            $doc = new DOMDocument();
            $doc->load($filename);
            
            // Locate the root element
            $root = $doc->documentElement;

            // Create a new <contact> element
            $newContact = $doc->createElement('contact');

            // Add child elements to the new contact
            $fName = $doc->createElement('firstName', $_SESSION['first_name']);
            $newContact->appendChild($fName);
            $lname = $doc->createElement('lastName', $_SESSION['last_name']);
            $newContact->appendChild($lname);
            $phoneNo = $doc->createElement('phoneNo', $_SESSION['phone-no']);
            $newContact->appendChild($phoneNo);
            $gender = $doc->createElement('gender', $_SESSION['gender']);
            $newContact->appendChild($gender);
            $email = $doc->createElement('email', $_SESSION['email']);
            $newContact->appendChild($email);
            $comment = $doc->createElement('comment', $_SESSION['comment']);
            $newContact->appendChild($comment);
            $contactID = $doc->createElement('contactID', $_SESSION['contactID']);
            $newContact->appendChild($contactID);

            // Append the new contact to the root element
            $root->appendChild($newContact);

            // Format the output and save changes
            $doc->formatOutput = true;
            $doc->save($filename);
            echo "New contact added!";
            ?>
        }

        // // Validate first name
        // if (!nameRegex.test(fname)) {
        //     alert('First name should be a sequence of letters and the first letter must be upper case');
        //     event.preventDefault();
        //     return;
        // }

        // // Validate last name
        // if (!nameRegex.test(lname)) {
        //     alert('Last name should be a sequence of letters and the first letter must be upper case');
        //     event.preventDefault();
        //     return;
        // }

        // if (fname.toLowerCase()===lname.toLowerCase()) { //if (!equalNameRegex.test(lname)) {
        //     alert('First and last name cannot be the same');
        //     event.preventDefault();
        //     return;
        // }

        // // Validate phone number
        // if (!phoneRegex.test(phoneNo)) {
        //     alert('Phone number should be formatted xxx-xxx-xxxx');
        //     event.preventDefault();
        //     return;
        // }

        // // Validate email
        // if (!emailRegex.test(email)) {
        //     alert('Email must have @ and . (example123@example.com)');
        //     event.preventDefault();
        //     return;
        // }

        // Validate comment
        if (!commentRegex.test(comment)) {
            alert('Comment must have at least 10 characters');
            event.preventDefault();
            return;
        } 

        // const genderSelected = document.querySelector('input[name="gender"]:checked');
        // if (!genderSelected) {
        //     alert("Please select a gender");
        //     event.preventDefault();
        //     return;
        // }

        else {
            
            event.preventDefault();
            let xmlString = createXML(comment, contactID);
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "/write-contacts", true);
            xhr.setRequestHeader("Content-Type", "application/xml");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('display-details').innerHTML = `<h4> Thanks for contacting us! </h4>`;
                }
            };
            xhr.onerror = function() {
                alert("Unable to save your contact details.");
            };
            xhr.send(xmlString);
        }
    });
</script>