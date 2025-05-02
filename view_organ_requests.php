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

// Fetch all organ requests along with recipient's name, blood group, phone number, and current status
$sql = "SELECT r.recipientID, a.firstname, a.lastname, r.organType, r.status AS requestStatus, r.requestDate, 
               a.bloodGroup, a.phone, t.status AS transplantStatus 
        FROM organ_requests r
        JOIN accountInfo a ON r.recipientID = a.userID
        LEFT JOIN transplantInfo t ON r.recipientID = t.recipientID AND r.organType = t.organType
        ORDER BY r.requestDate DESC";  // Optional: you can sort by request date

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Organ Requests</title>
</head>
<body>
    <h1>Organ Requests</h1>

    <table border="1">
        <tr>
            <th>Recipient ID</th>
            <th>Recipient Name</th>
            <th>Organ Type</th>
            <th>Blood Group</th>
            <th>Phone Number</th>
            <th>Status</th>
            <th>Transplant Status</th> <!-- Showing transplant status as well -->
            <th>Request Date</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // If there is no matching transplant status, the transplantStatus will be NULL
                $transplantStatus = $row['transplantStatus'] ? htmlspecialchars($row['transplantStatus']) : 'Not yet Transplanted';
                echo "<tr>
                        <td>" . htmlspecialchars($row['recipientID']) . "</td>
                        <td>" . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . "</td>
                        <td>" . htmlspecialchars($row['organType']) . "</td>
                        <td>" . htmlspecialchars($row['bloodGroup']) . "</td>
                        <td>" . htmlspecialchars($row['phone']) . "</td>
                        <td>" . htmlspecialchars($row['requestStatus']) . "</td>
                        <td>" . $transplantStatus . "</td>
                        <td>" . htmlspecialchars($row['requestDate']) . "</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No organ requests found.</td></tr>";
        }
        ?>
    </table>

    <a href="doctor_dashboard.php">Back to Dashboard</a> | <a href="logout.php">Logout</a>
</body>
</html>

<?php
$conn->close();
?>
