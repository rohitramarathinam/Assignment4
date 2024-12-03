<?php
include 'db.php';  // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure each form field is set and not empty
    $first_name = isset($_POST['first-name']) ? $_POST['first-name'] : null;
    $last_name = isset($_POST['last-name']) ? $_POST['last-name'] : null;
    $phone_no = isset($_POST['phone-no']) ? $_POST['phone-no'] : null;
    $dob = isset($_POST['dob']) ? $_POST['dob'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $gender = isset($_POST['gender']) ? $_POST['gender'] : null;
    $password = isset($_POST['pwd']) ? $_POST['pwd'] : null;
    $confirm_password = isset($_POST['confirm-pwd']) ? $_POST['confirm-pwd'] : null;

    // Check if any required fields are missing
    if (empty($first_name) || empty($last_name) || empty($phone_no) || empty($dob) || empty($email) || empty($password)) {
        echo "All required fields must be filled!";
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    // Validate phone number format (must be ddd-ddd-dddd)
    if (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone_no)) {
        echo "Phone number format must be ddd-ddd-dddd.";
        exit;
    }

    // Check password length (minimum 8 characters)
    if (strlen($password) < 8) {
        echo "Password must be at least 8 characters.";
        exit;
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, phone_no, dob, email, gender, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $first_name, $last_name, $phone_no, $dob, $email, $gender, $hashed_password);

    if ($stmt->execute()) {
        header("Location: register.html?status=success");
        exit;
    } else {
        header("Location: register.html?status=error");
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
