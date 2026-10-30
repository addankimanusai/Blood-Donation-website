<?php
/**
 * Admin - Export Data
 */

require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$conn = getDBConnection();

if (isset($_GET['type']) && $_GET['type'] === 'donors') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=donors_' . date('Y-m-d') . '.csv');

    $sql = "SELECT id, name, age, phone, gender, blood_group, state, city, created_at FROM donors WHERE is_active = TRUE ORDER BY created_at DESC";
    $result = $conn->query($sql);

    echo "ID,Name,Age,Phone,Gender,Blood Group,State,City,Registered\n";
    while ($row = $result->fetch_assoc()) {
        echo implode(',', [
            $row['id'],
            '"' . str_replace('"', '""', $row['name']) . '"',
            $row['age'],
            $row['phone'],
            $row['gender'],
            $row['blood_group'],
            '"' . str_replace('"', '""', $row['state']) . '"',
            '"' . str_replace('"', '""', $row['city']) . '"',
            $row['created_at']
        ]) . "\n";
    }
    exit;
}

if (isset($_GET['type']) && $_GET['type'] === 'appointments') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=appointments_' . date('Y-m-d') . '.csv');

    $sql = "SELECT a.id, d.name as donor_name, a.appointment_date, a.appointment_time, a.location, a.status
            FROM appointments a
            JOIN donors d ON a.donor_id = d.id
            ORDER BY a.appointment_date DESC";
    $result = $conn->query($sql);

    echo "ID,Donor,Date,Time,Location,Status\n";
    while ($row = $result->fetch_assoc()) {
        echo implode(',', [
            $row['id'],
            '"' . str_replace('"', '""', $row['donor_name']) . '"',
            $row['appointment_date'],
            $row['appointment_time'],
            '"' . str_replace('"', '""', $row['location']) . '"',
            $row['status']
        ]) . "\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - Blood Bond</title>
    <link rel="stylesheet" href="../../styles.css?v=20260814">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="../../index.html" class="logo" aria-label="Blood Bond home"><span class="brand-mark" aria-hidden="true">+</span><span>BLOOD BOND</span></a>
            <div class="navbar-actions">
                <a href="index.php" class="btn">Dashboard</a>
            </div>
        </nav>
    </header>

    <div class="admin-container">
        <h2>Export Data</h2>

        <div class="export-card">
            <h3>Donors</h3>
            <p>Export all donor records as CSV</p>
            <div class="export-links">
                <a href="export.php?type=donors" class="btn">Download CSV</a>
            </div>
        </div>

        <div class="export-card">
            <h3>Appointments</h3>
            <p>Export all appointments as CSV</p>
            <div class="export-links">
                <a href="export.php?type=appointments" class="btn">Download CSV</a>
            </div>
        </div>
    </div>
</body>
</html>
