<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Recipient') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "organ_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userID = $_SESSION['userID'];
$sql = "SELECT * FROM accountInfo WHERE userID = ? AND userType = 'Recipient'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$userInfo = $result->fetch_assoc();
$stmt->close();

// Fetch transplant records for the recipient, including Donor's Name and Doctor's Name, and their IDs
$transplantRecordsSql = "SELECT t.transplantID, t.organType, t.date, t.status, 
                                d.name AS doctorName, d.doctorID AS doctorID, 
                                don.firstname AS donorFirstName, don.lastname AS donorLastName, don.userID AS donorID
                         FROM transplantInfo t 
                         JOIN doctorInfo d ON t.doctorID = d.doctorID
                         JOIN accountInfo don ON t.donorID = don.userID
                         WHERE t.recipientID = ?";
$transplantStmt = $conn->prepare($transplantRecordsSql);
$transplantStmt->bind_param("i", $userID);
$transplantStmt->execute();
$transplantResult = $transplantStmt->get_result();
$transplantRecords = [];
while ($row = $transplantResult->fetch_assoc()) {
    $transplantRecords[] = $row;
}
$transplantStmt->close();

// Fetch the organ requests made by the recipient, including Request ID
$requestSql = "SELECT requestID, organType, bloodGroup FROM organ_requests WHERE recipientID = ?";
$requestStmt = $conn->prepare($requestSql);
$requestStmt->bind_param("i", $userID);
$requestStmt->execute();
$requestResult = $requestStmt->get_result();
$requestList = [];
while ($row = $requestResult->fetch_assoc()) {
    $requestList[] = $row;
}
$requestStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>Recipient Dashboard</h1>

    <!-- Display recipient's personal information in a table -->
    <h2>Your Information</h2>
    <?php if ($userInfo): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <td><?php echo htmlspecialchars($userInfo['userID']); ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($userInfo['firstname'] . ' ' . $userInfo['lastname']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($userInfo['email']); ?></td>
            </tr>
            <tr>
                <th>Age</th>
                <td><?php echo htmlspecialchars($userInfo['age']); ?></td>
            </tr>
            <tr>
                <th>Gender</th>
                <td><?php echo htmlspecialchars($userInfo['gender']); ?></td>
            </tr>
            <tr>
                <th>Blood Group</th>
                <td><?php echo htmlspecialchars($userInfo['bloodGroup']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($userInfo['phone']); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo htmlspecialchars($userInfo['address']); ?></td>
            </tr>
        </table>
    <?php else: ?>
        <p>Information not available.</p>
    <?php endif; ?>

    <!-- Display transplant records -->
    <h2>Transplant Records</h2>
    <?php if (count($transplantRecords) > 0): ?>
        <table border="1">
            <tr>
                <th>Transplant ID</th>
                <th>Organ Type</th>
                <th>Transplant Date</th>
                <th>Doctor Name (ID)</th>
                <th>Donor Name (ID)</th>
                <th>Status</th>
            </tr>
            <?php foreach ($transplantRecords as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['transplantID']); ?></td>
                    <td><?php echo htmlspecialchars($record['organType']); ?></td>
                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                    <td><?php echo htmlspecialchars($record['doctorName']) . " (ID: " . htmlspecialchars($record['doctorID']) . ")"; ?></td>
                    <td><?php echo htmlspecialchars($record['donorFirstName'] . ' ' . $record['donorLastName']) . " (ID: " . htmlspecialchars($record['donorID']) . ")"; ?></td>
                    <td><?php echo htmlspecialchars($record['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No transplant records available.</p>
    <?php endif; ?>

    <!-- Display requested organs with Request ID -->
    <h2>Your Requested Organs</h2>
    <?php if (count($requestList) > 0): ?>
        <table border="1">
            <tr>
                <th>Request ID</th>
                <th>Organ Type</th>
                <th>Blood Group</th>
            </tr>
            <?php foreach ($requestList as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['requestID']); ?></td>
                    <td><?php echo htmlspecialchars($request['organType']); ?></td>
                    <td><?php echo htmlspecialchars($request['bloodGroup']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No organ requests made yet.</p>
    <?php endif; ?>

    <!-- Options for recipient -->
    <ul>
        <li><a href="search_donors.php?search=blood_group">Search Available Donors with Blood Groups</a></li>
        <li><a href="request_organ.php">Request an Organ</a></li>
    </ul>

    <a href="logout.php">Logout</a>
</div>

</body>
</html>

<?php
$conn->close();
?>
