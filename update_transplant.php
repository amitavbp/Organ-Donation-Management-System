<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: doctor_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "organ_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle transplant status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateTransplant'])) {
    $transplantID = $_POST['transplantID'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    // Update transplant record status and date
    $stmt = $conn->prepare("UPDATE transplantInfo SET date = ?, status = ? WHERE transplantID = ?");
    $stmt->bind_param("ssi", $date, $status, $transplantID);

    if ($stmt->execute()) {
        $message = "Transplant record updated successfully.";

        // Get the related donorID, recipientID, and organType
        $fetchDetails = $conn->prepare("SELECT donorID, recipientID, organType FROM transplantInfo WHERE transplantID = ?");
        $fetchDetails->bind_param("i", $transplantID);
        $fetchDetails->execute();
        $fetchDetails->bind_result($donorID, $recipientID, $organType);
        $fetchDetails->fetch();
        $fetchDetails->close();

        // If the status is not 'Pending', update organ availability to 'No'
        if ($status !== 'Pending') {
            $updateOrganAvailability = $conn->prepare("UPDATE organs SET availability = 'No' WHERE donorID = ? AND organType = ?");
            $updateOrganAvailability->bind_param("is", $donorID, $organType);
            $updateOrganAvailability->execute();
        }

        // If the status is 'Completed', update organ requests
        if ($status === 'Completed') {
            // Update recipient's organ request status to 'Completed'
            $updateRequestStatus = $conn->prepare("UPDATE organ_requests SET status = 'Completed' WHERE recipientID = ? AND organType = ? AND status = 'Pending'");
            $updateRequestStatus->bind_param("is", $recipientID, $organType);
            $updateRequestStatus->execute();
        }
    } else {
        $message = "Error updating transplant record: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Transplant Record</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>Update Transplant Record</h1>

    <!-- Display success/error message -->
    <?php if (!empty($message)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Form to update transplant record -->
    <form method="POST" action="">
        <input type="hidden" name="updateTransplant" value="1">
        <label for="transplantID">Transplant ID:</label>
        <input type="number" id="transplantID" name="transplantID" required><br>

        <label for="date">Transplant Date:</label>
        <input type="date" id="date" name="date" required><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="Pending">Pending</option>
            <option value="Scheduled">Scheduled</option>
            <option value="Failed">Failed</option>
            <option value="Completed">Completed</option>
        </select><br>

        <button type="submit">Update Transplant Record</button>
    </form>

    <a href="doctor_dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>
