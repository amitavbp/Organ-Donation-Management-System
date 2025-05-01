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

// Message to display after operations
$message = "";

// Handle addition of new transplant records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addTransplant'])) {
    $donorID = $_POST['donorID'];
    $recipientID = $_POST['recipientID'];
    $organType = $_POST['organType'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $doctorID = $_SESSION['userID'];

    // Insert into transplantInfo table
    $stmt = $conn->prepare("INSERT INTO transplantInfo (donorID, recipientID, doctorID, organType, date, status) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisss", $donorID, $recipientID, $doctorID, $organType, $date, $status);

    if ($stmt->execute()) {
        $message = "Transplant record added successfully.";

        // Update organ request status to match transplant status for the corresponding organType and recipientID
        $updateRequestStatus = $conn->prepare("UPDATE organ_requests SET status = ? WHERE recipientID = ? AND organType = ?");
        $updateRequestStatus->bind_param("sis", $status, $recipientID, $organType);
        $updateRequestStatus->execute();
    } else {
        $message = "Error adding transplant record: " . $stmt->error;
    }
    $stmt->close();
}

// Handle updating of existing transplant records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateTransplant'])) {
    $transplantID = $_POST['transplantID'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    // Update transplant record status and date
    $stmt = $conn->prepare("UPDATE transplantInfo SET date = ?, status = ? WHERE transplantID = ?");
    $stmt->bind_param("ssi", $date, $status, $transplantID);

    if ($stmt->execute()) {
        $message = "Transplant record updated successfully.";

        // Get the related recipientID and organType
        $fetchDetails = $conn->prepare("SELECT recipientID, organType FROM transplantInfo WHERE transplantID = ?");
        $fetchDetails->bind_param("i", $transplantID);
        $fetchDetails->execute();
        $fetchDetails->bind_result($recipientID, $organType);
        $fetchDetails->fetch();
        $fetchDetails->close();

        // Update organ request status to match transplant status for the corresponding organType and recipientID
        $updateRequestStatus = $conn->prepare("UPDATE organ_requests SET status = ? WHERE recipientID = ? AND organType = ?");
        $updateRequestStatus->bind_param("sis", $status, $recipientID, $organType);
        $updateRequestStatus->execute();
    } else {
        $message = "Error updating transplant record: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing transplant records, including organ type
$sql = "SELECT t.transplantID, t.donorID, t.recipientID, t.doctorID, ai.firstname AS donor_name, ai.lastname AS donor_lastname, 
               ri.firstname AS recipient_name, ri.lastname AS recipient_lastname, d.name AS doctor_name, t.date, t.status, t.organType
        FROM transplantInfo t
        JOIN accountInfo ai ON t.donorID = ai.userID
        JOIN accountInfo ri ON t.recipientID = ri.userID
        JOIN doctorInfo d ON t.doctorID = d.doctorID";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transplant Records</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="container">
    <h1>Transplant Records</h1>

    <!-- Display success/error message -->
    <?php if (!empty($message)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Form to add new transplant record -->
    <h3>Add New Transplant Record:</h3>
    <form method="POST" action="">
        <input type="hidden" name="addTransplant" value="1">
        <label for="donorID">Donor ID:</label>
        <input type="number" id="donorID" name="donorID" required><br>

        <label for="recipientID">Recipient ID:</label>
        <input type="number" id="recipientID" name="recipientID" required><br>

        <label for="organType">Organ Type:</label>
        <select id="organType" name="organType" required>
            <option value="Heart">Heart</option>
            <option value="Kidney">Kidney</option>
            <option value="Liver">Liver</option>
            <option value="Lungs">Lungs</option>
            <option value="Cornea">Cornea</option>
        </select><br>

        <label for="date">Transplant Date:</label>
        <input type="date" id="date" name="date" required><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="Pending">Pending</option>
            <option value="Scheduled">Scheduled</option>
            <option value="Failed">Failed</option>
            <option value="Completed">Completed</option>
        </select><br>

        <button type="submit">Add Transplant Record</button>
    </form>

    <!-- Form to update existing transplant record -->
    <h3>Update Transplant Record:</h3>
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

    <!-- Table to display existing transplant records -->
    <h3>Existing Transplant Records:</h3>
    <table border="1">
        <tr>
            <th>Transplant ID</th>
            <th>Donor ID</th>
            <th>Recipient ID</th>
            <th>Doctor ID</th>
            <th>Donor Name</th>
            <th>Recipient Name</th>
            <th>Doctor Name</th>
            <th>Organ Type</th>
            <th>Date</th>
            <th>Status</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['transplantID']) . "</td>
                        <td>" . htmlspecialchars($row['donorID']) . "</td>
                        <td>" . htmlspecialchars($row['recipientID']) . "</td>
                        <td>" . htmlspecialchars($row['doctorID']) . "</td>
                        <td>" . htmlspecialchars($row['donor_name']) . " " . htmlspecialchars($row['donor_lastname']) . "</td>
                        <td>" . htmlspecialchars($row['recipient_name']) . " " . htmlspecialchars($row['recipient_lastname']) . "</td>
                        <td>" . htmlspecialchars($row['doctor_name']) . "</td>
                        <td>" . htmlspecialchars($row['organType']) . "</td>
                        <td>" . htmlspecialchars($row['date']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='10'>No transplant records found.</td></tr>";
        }
        ?>
    </table>

    <!-- Link to go back to the dashboard -->
    <a href="doctor_dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>
