<?php
session_start();
if (!isset($_SESSION['userID']) || ($_SESSION['role'] !== 'Doctor' && $_SESSION['role'] !== 'Recipient')) {
    header("Location: doctor_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "organ_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search parameters only when the form is submitted
$organType = isset($_GET['organType']) ? $_GET['organType'] : '';
$bloodGroup = isset($_GET['bloodGroup']) ? $_GET['bloodGroup'] : '';

$searchResults = []; // Initialize the search results variable

// Only run the query if both parameters are set (i.e., the form is submitted)
if ($organType !== '' || $bloodGroup !== '') {
    // Search query for donors based on organ type and blood group
    $sql = "SELECT u.userID, ai.firstname, ai.lastname, ai.bloodGroup, ai.phone, ai.address, ai.email, o.organType 
            FROM userAccount u
            JOIN accountInfo ai ON u.userID = ai.userID
            JOIN organs o ON u.userID = o.donorID
            WHERE o.availability = 'Yes' ";

    if ($organType != '') {
        $sql .= "AND o.organType LIKE ? ";
    }

    if ($bloodGroup != '') {
        $sql .= "AND ai.bloodGroup = ? ";
    }

    $stmt = $conn->prepare($sql);

    if ($organType != '' && $bloodGroup != '') {
        $stmt->bind_param('ss', $organType, $bloodGroup);
    } elseif ($organType != '') {
        $stmt->bind_param('s', $organType);
    } elseif ($bloodGroup != '') {
        $stmt->bind_param('s', $bloodGroup);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Store search results
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Available Donors</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="container">
    <h1>Search Available Donors</h1>

    <!-- Display search form only for Doctors and Recipients -->
    <?php if ($_SESSION['role'] === 'Doctor' || $_SESSION['role'] === 'Recipient') { ?>
        <form method="GET" action="search_donors.php">
            <label for="organType">Organ Type:</label>
            <select name="organType" required>
                <option value="Kidney" <?php echo ($organType == 'Kidney') ? 'selected' : ''; ?>>Kidney</option>
                <option value="Liver" <?php echo ($organType == 'Liver') ? 'selected' : ''; ?>>Liver</option>
                <option value="Heart" <?php echo ($organType == 'Heart') ? 'selected' : ''; ?>>Heart</option>
                <option value="Lungs" <?php echo ($organType == 'Lungs') ? 'selected' : ''; ?>>Lungs</option>
                <option value="Cornea" <?php echo ($organType == 'Cornea') ? 'selected' : ''; ?>>Cornea</option>
            </select>

            <label for="bloodGroup">Blood Group:</label>
            <select name="bloodGroup" required>
                <option value="A+" <?php echo ($bloodGroup == 'A+') ? 'selected' : ''; ?>>A+</option>
                <option value="A-" <?php echo ($bloodGroup == 'A-') ? 'selected' : ''; ?>>A-</option>
                <option value="B+" <?php echo ($bloodGroup == 'B+') ? 'selected' : ''; ?>>B+</option>
                <option value="B-" <?php echo ($bloodGroup == 'B-') ? 'selected' : ''; ?>>B-</option>
                <option value="AB+" <?php echo ($bloodGroup == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                <option value="AB-" <?php echo ($bloodGroup == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                <option value="O+" <?php echo ($bloodGroup == 'O+') ? 'selected' : ''; ?>>O+</option>
                <option value="O-" <?php echo ($bloodGroup == 'O-') ? 'selected' : ''; ?>>O-</option>
            </select>

            <button type="submit">Search</button>
        </form>
    <?php } ?>

    <h2>Search Results</h2>

    <!-- Table to display results based on role -->
    <?php if (!empty($searchResults)) { ?>
        <table border="1">
            <tr>
                <?php if ($_SESSION['role'] === 'Doctor') { ?>
                    <th>Donor ID</th>
                    <th>Donor Name</th>
                    <th>Blood Group</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Organ Type</th>
                <?php } elseif ($_SESSION['role'] === 'Recipient') { ?>
                    <th>Donor ID</th>
                    <th>Donor Name</th>
                    <th>Blood Group</th>
                    <th>Phone</th>
                    <th>Organ Type</th>
                <?php } ?>
            </tr>

            <?php
            foreach ($searchResults as $row) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['userID']) . "</td> <!-- Donor ID -->
                        <td>" . htmlspecialchars($row['firstname']) . " " . htmlspecialchars($row['lastname']) . "</td>
                        <td>" . htmlspecialchars($row['bloodGroup']) . "</td>
                        <td>" . htmlspecialchars($row['phone']) . "</td>";
                if ($_SESSION['role'] === 'Doctor') {
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>
                          <td>" . htmlspecialchars($row['address']) . "</td>
                          <td>" . htmlspecialchars($row['organType']) . "</td>";
                } elseif ($_SESSION['role'] === 'Recipient') {
                    echo "<td>" . htmlspecialchars($row['organType']) . "</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>
    <?php } else { ?>
        <p>No results found. Please enter search criteria.</p>
    <?php } ?>

    <!-- Back to Dashboard and Logout -->
    <div class="links">
        <?php if ($_SESSION['role'] === 'Doctor') { ?>
            <a href="doctor_dashboard.php">Back to Doctor Dashboard</a>
        <?php } elseif ($_SESSION['role'] === 'Recipient') { ?>
            <a href="recipient_dashboard.php">Back to Recipient Dashboard</a>
        <?php } ?>
        | <a href="logout.php">Logout</a>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
