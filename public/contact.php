<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['first_name'])) {
    header("Location: login.html");
    exit;
}

$filename = 'saved-contacts.xml';

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Initialize the DOMDocument
$doc = new DOMDocument();
if (file_exists($filename)) {
    $doc->load($filename);
} else {
    // Create a new XML structure if the file doesn't exist
    $root = $doc->createElement('contacts');
    $doc->appendChild($root);
    $doc->formatOutput = true;
    $doc->save($filename);
    $doc->load($filename);
}

// Get the root element
$root = $doc->documentElement;
if (!$root) {
    $root = $doc->createElement('contacts');
    $doc->appendChild($root);
}

// Handle POST request from AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    $contactID = isset($_POST['contactID']) ? $_POST['contactID'] : '';

    // Create a new <contact> element
    $newContact = $doc->createElement('contact');

    // Add child elements
    $fName = $doc->createElement('firstName', $_SESSION['first_name']);
    $newContact->appendChild($fName);
    $lName = $doc->createElement('lastName', $_SESSION['last_name']);
    $newContact->appendChild($lName);
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'Unknown';
    $emailElement = $doc->createElement('email', $email);
    $newContact->appendChild($emailElement);
    $commentElement = $doc->createElement('comment', htmlspecialchars($comment));
    $newContact->appendChild($commentElement);
    $contactIDElement = $doc->createElement('contactID', htmlspecialchars($contactID));
    $newContact->appendChild($contactIDElement);

    // Append the new contact to the root element
    $root->appendChild($newContact);

    // Save changes
    $doc->formatOutput = true;
    $doc->save($filename);

    // Send success response
    echo json_encode(['status' => 'success', 'message' => 'Contact saved successfully']);
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
        event.preventDefault(); // Prevent default form submission

        const comment = document.getElementById('comment').value;
        const contactID = Math.floor(Math.random() * 1e6);

        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment=${encodeURIComponent(comment)}&contactID=${encodeURIComponent(contactID)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('display-details').innerHTML = `<h4>${data.message}</h4>`;
            } else {
                alert('Failed to save contact');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the contact');
        });
    });

</script>