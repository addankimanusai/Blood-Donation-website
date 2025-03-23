<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Blood Donation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffe6e6;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        h2 {
            color: #b30000;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            max-width: 800px;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #ffffff;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ff6666;
        }

        th {
            background-color: #ff4d4d;
            color: #fff;
        }

        td {
            color: #b30000;
        }

        .no-results {
            font-size: 18px;
            color: #b30000;
            margin-top: 20px;
        }

        .go-back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #b30000;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-align: center;
        }

        .go-back-button:hover {
            background-color: #800000;
        }
    </style>
</head>
<body>

    <h2>Search Results</h2>

    <?php
    include 'db_connect.php'; // Include the database connection

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['searchDonors'])) {
        // Get the search criteria from the form
        $bloodGroup = $_POST['searchBloodGroup'];
        $state = $_POST['searchState'];
        $city = $_POST['searchCity'];

        // Build the query dynamically based on the search criteria
        $query = "SELECT * FROM donors WHERE 1=1";
        if (!empty($bloodGroup)) {
            $query .= " AND blood_group = ?";
        }
        if (!empty($state)) {
            $query .= " AND state = ?";
        }
        if (!empty($city)) {
            $query .= " AND city = ?";
        }

        // Prepare the SQL statement
        $stmt = $conn->prepare($query);

        // Bind parameters dynamically based on the input fields
        $types = '';
        $params = [];
        if (!empty($bloodGroup)) {
            $types .= 's';
            $params[] = $bloodGroup;
        }
        if (!empty($state)) {
            $types .= 's';
            $params[] = $state;
        }
        if (!empty($city)) {
            $types .= 's';
            $params[] = $city;
        }

        // Bind the parameters to the statement
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if any donors match the search criteria
        if ($result->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Phone Number</th>
                        <th>Gender</th>
                        <th>Blood Group</th>
                        <th>State</th>
                        <th>City</th>
                    </tr>";

            // Fetch and display each matching donor in a row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['age']) . "</td>
                        <td>" . htmlspecialchars($row['phone']) . "</td>
                        <td>" . htmlspecialchars($row['gender']) . "</td>
                        <td>" . htmlspecialchars($row['blood_group']) . "</td>
                        <td>" . htmlspecialchars($row['state']) . "</td>
                        <td>" . htmlspecialchars($row['city']) . "</td>
                    </tr>";
            }

            echo "</table>";
        } else {
            echo "<p class='no-results'>No matching donors found.</p>";
        }

        // Close the prepared statement and connection
        $stmt->close();
    }
    $conn->close();
    ?>

    <!-- Go Back to Home Button -->
    <a href="http://localhost/donorsdatabase/index.html#search" class="go-back-button">Go Back to Home</a>

</body>
</html>
