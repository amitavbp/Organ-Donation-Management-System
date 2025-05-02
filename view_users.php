<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>View Registered Users</h1>

    <!-- Buttons to view donors and recipients -->
    <form action="view_donors.php" method="GET">
        <button type="submit">View Donors</button>
    </form>
    <form action="view_recipients.php" method="GET">
        <button type="submit">View Recipients</button>
    </form>
    <form action="view_all_users.php" method="GET">
        <button type="submit">View All Users</button>
    </form>

    <div class="links">
        <a href="doctor_dashboard.php">Back to Dashboard</a>
    </div>
</div>

</body>
</html>
