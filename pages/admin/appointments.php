<?php
/**
 * Admin - Manage Appointments
 */

require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$conn = getDBConnection();

$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$date = isset($_GET['date']) ? sanitize($_GET['date']) : '';

$sql = "SELECT a.*, d.name as donor_name, d.phone as donor_phone, d.blood_group
        FROM appointments a
        JOIN donors d ON a.donor_id = d.id
        WHERE 1=1";
$params = [];
$types = '';

if ($status) {
    $sql .= " AND a.status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($date) {
    $sql .= " AND a.appointment_date = ?";
    $params[] = $date;
    $types .= 's';
}

$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 100";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Blood Bond</title>
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
        <h2>Manage Appointments</h2>

        <form method="GET" class="filters">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <input type="date" name="date" value="<?= $date ?>">
            <button type="submit">Filter</button>
            <a href="appointments.php" class="btn" style="background:var(--text-secondary);">Clear</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Donor</th>
                    <th>Blood</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                <tr><td colspan="8" style="text-align:center;">No appointments found</td></tr>
                <?php else: ?>
                <?php foreach ($appointments as $appt): ?>
                <tr>
                    <td><?= $appt['id'] ?></td>
                    <td><?= htmlspecialchars($appt['donor_name']) ?><br><small><?= $appt['donor_phone'] ?></small></td>
                    <td><?= $appt['blood_group'] ?></td>
                    <td><?= formatDate($appt['appointment_date'], 'd M Y') ?></td>
                    <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                    <td><?= htmlspecialchars($appt['location']) ?></td>
                    <td><span class="status-badge status-<?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span></td>
                    <td>
                        <?php if ($appt['status'] === 'pending'): ?>
                        <button onclick="updateStatus(<?= $appt['id'] ?>, 'confirmed')" class="btn-small" style="background:#28a745;">Confirm</button>
                        <?php endif; ?>
                        <?php if ($appt['status'] !== 'completed' && $appt['status'] !== 'cancelled'): ?>
                        <button onclick="updateStatus(<?= $appt['id'] ?>, 'cancelled')" class="btn-small" style="background:#dc3545;">Cancel</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    async function updateStatus(id, status) {
        if (!confirm('Update appointment status to ' + status + '?')) return;

        try {
            const response = await fetch('../../api/appointments.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });

            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert('Update failed');
            }
        } catch (error) {
            alert('Network error');
        }
    }
    </script>
</body>
</html>
