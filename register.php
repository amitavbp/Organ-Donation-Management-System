<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "organ_management");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $userType = $_POST['userType'];  // 'Donor' or 'Recipient'
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $bloodGroup = $_POST['bloodGroup'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO userAccount (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $userType);

    if ($stmt->execute()) {
        $userID = $conn->insert_id;
        $stmt2 = $conn->prepare("INSERT INTO accountInfo (userID, firstname, lastname, email, age, gender, bloodGroup, phone, address, userType)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("isssisssss", $userID, $firstname, $lastname, $email, $age, $gender, $bloodGroup, $phone, $address, $userType);

        if ($stmt2->execute()) {
            echo "Registration successful!";
        } else {
            echo "Error in accountInfo: " . $stmt2->error;
        }
        $stmt2->close();
    } else {
        echo "Error in userAccount: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor/Recipient Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Register as Donor or Recipient</h1>
    <form method="POST" action="">
        <label for="userType">Register as:</label>
        <select name="userType" id="userType" required>
            <option value="Donor">Donor</option>
            <option value="Recipient">Recipient</option>
        </select><br>
        
        <input type="text" name="firstname" placeholder="First Name" required><br>
        <input type="text" name="lastname" placeholder="Last Name" required><br>
        
        <label for="age">Age:</label>
        <input type="number" name="age" placeholder="Age" required><br>
        
        <label for="gender">Gender:</label>
        <select name="gender" id="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select><br>
        
        <label for="bloodGroup">Blood Group:</label>
        <select name="bloodGroup" id="bloodGroup" required>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
        </select><br>

        <h2>Contact Information</h2>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="tel" name="phone" placeholder="Phone" required><br>
        <input type="text" name="address" placeholder="Address" required><br>
        
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
