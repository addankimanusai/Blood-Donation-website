<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Blood Bond</title>
    <link rel="stylesheet" href="styles.css?v=20260814">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="index.html" class="logo" aria-label="Blood Bond home"><span class="brand-mark" aria-hidden="true">+</span><span>BLOOD BOND</span></a>
            <div class="navbar-actions">
                <a href="index.html" class="btn">Back Home</a>
            </div>
        </nav>
    </header>

    <main class="search-results-page">
        <h2>Search Results</h2>

        <div class="results-shell">
            <?php
            include 'db_connect.php';

            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $bloodGroup = isset($_POST['searchBloodGroup']) ? $_POST['searchBloodGroup'] : '';
                $state = isset($_POST['searchState']) ? $_POST['searchState'] : '';
                $city = isset($_POST['searchCity']) ? $_POST['searchCity'] : '';

                $query = "SELECT * FROM donors WHERE 1=1";
                $types = '';
                $params = [];

                if (!empty($bloodGroup)) {
                    $query .= " AND blood_group = ?";
                    $types .= 's';
                    $params[] = $bloodGroup;
                }
                if (!empty($state)) {
                    $query .= " AND state = ?";
                    $types .= 's';
                    $params[] = $state;
                }
                if (!empty($city)) {
                    $query .= " AND city = ?";
                    $types .= 's';
                    $params[] = $city;
                }

                $stmt = $conn->prepare($query);
                if ($types) {
                    $stmt->bind_param($types, ...$params);
                }

                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo '<table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Phone Number</th>
                                    <th>Gender</th>
                                    <th>Blood Group</th>
                                    <th>State</th>
                                    <th>City</th>
                                </tr>
                            </thead>
                            <tbody>';

                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>
                                <td>' . htmlspecialchars($row['name']) . '</td>
                                <td>' . htmlspecialchars($row['age']) . '</td>
                                <td>' . htmlspecialchars($row['phone']) . '</td>
                                <td>' . htmlspecialchars($row['gender']) . '</td>
                                <td>' . htmlspecialchars($row['blood_group']) . '</td>
                                <td>' . htmlspecialchars($row['state']) . '</td>
                                <td>' . htmlspecialchars($row['city']) . '</td>
                              </tr>';
                    }

                    echo '</tbody></table>';
                } else {
                    echo '<p class="no-results text-center">No matching donors found.</p>';
                }

                $stmt->close();
            } else {
                echo '<p class="no-results text-center">Use the search form on the home page to find matching donors.</p>';
            }

            $conn->close();
            ?>
        </div>

        <div class="pagination">
            <a href="index.html#search" class="btn">Try Another Search</a>
            <a href="index.html" class="btn btn-secondary">Go Back to Home</a>
        </div>
    </main>
</body>
</html>
