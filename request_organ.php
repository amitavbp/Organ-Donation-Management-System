<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Recipient') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture the data from the form
    $recipientID = $_SESSION['userID'];
    $organType = $_POST['organType'];
    $bloodGroup = $_POST['bloodGroup']; // Added blood group field

    // Create a connection to the database
    $conn = new mysqli("localhost", "root", "", "organ_management");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the organ request already exists for this recipient
    $checkStmt = $conn->prepare("SELECT * FROM organ_requests WHERE recipientID = ? AND organType = ?");
    $checkStmt->bind_param("is", $recipientID, $organType);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // Organ request already exists
        echo "You have already requested this organ.";
    } else {
        // Insert the organ request into the organ_requests table
        $stmt = $conn->prepare("INSERT INTO organ_requests (recipientID, organType, bloodGroup) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $recipientID, $organType, $bloodGroup);
        if ($stmt->execute()) {
            echo "Organ request submitted successfully.";
        } else {
            echo "Error submitting request.";
        }
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request an Organ</title>
</head>
<body>

<h1>Request an Organ</h1>

<form method="POST" action="request_organ.php">
    <label for="organType">Organ Type:</label>
    <select name="organType" required>
        <option value="Kidney">Kidney</option>
        <option value="Liver">Liver</option>
        <option value="Heart">Heart</option>
        <option value="Lungs">Lungs</option>
        <option value="Cornea">Cornea</option>
    </select><br>

    <label for="bloodGroup">Blood Group:</label>
    <select name="bloodGroup" required>
        <option value="A+">A+</option>
        <option value="A-">A-</option>
        <option value="B+">B+</option>
        <option value="B-">B-</option>
        <option value="AB+">AB+</option>
        <option value="AB-">AB-</option>
        <option value="O+">O+</option>
        <option value="O-">O-</option>
    </select><br>

    <button type="submit">Submit Request</button>
</form>

<!-- Links for navigating back to the dashboard and logging out -->
<div class="links">
    <a href="recipient_dashboard.php">Back to Recipient Dashboard</a> | <a href="logout.php">Logout</a>
</div>

</body>
</html>
