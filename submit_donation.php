<?php
include 'db_connect.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $bloodGroup = $_POST['bloodGroup'];
    $state = $_POST['state'];
    $city = $_POST['city'];

    // Validate form data
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Donation Submission</title>
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #fdf2f2;
                color: #4d0000;
                text-align: center;
            }
            .response-container {
                background-color: #ffebeb;
                border: 2px solid #d9534f;
                border-radius: 12px;
                padding: 25px;
                max-width: 500px;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            }
            h1 {
                color: #d9534f;
                font-weight: 700;
                font-size: 2em;
                margin-bottom: 15px;
            }
            p {
                font-size: 1.1em;
                line-height: 1.6;
                color: #5c5c5c;
            }
            .button {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 24px;
                background-color: #d9534f;
                color: #ffffff;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                font-size: 1em;
                transition: background-color 0.3s ease;
            }
            .button:hover {
                background-color: #b52d2b;
            }
        </style>
    </head>
    <body>
        <div class='response-container'>";

    if (!empty($name) && !empty($age) && !empty($phone) && !empty($gender) && !empty($bloodGroup) && !empty($state) && !empty($city)) {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO donors (name, age, phone, gender, blood_group, state, city) VALUES (?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param("sisssss", $name, $age, $phone, $gender, $bloodGroup, $state, $city);

        // Execute query and provide feedback
        if ($stmt->execute()) {
            echo "<h1>Thank You!</h1>";
            echo "<p>Your donation information has been successfully submitted. You are making a real difference by helping save lives!</p>";
        } else {
            echo "<h1>Submission Failed</h1>";
            echo "<p>Unfortunately, there was an issue processing your request. Please try again later or contact support.</p>";
        }

        $stmt->close(); // Close the statement
    } else {
        echo "<h1>Form Incomplete</h1>";
        echo "<p>All fields are required. Please go back and complete the form to proceed with your donation registration.</p>";
    }

    echo "<a href='http://localhost/donorsdatabase/index.html#donate' class='button'>Return to Home</a>";
    echo "</div></body></html>";
}

$conn->close(); // Close the database connection
?>
