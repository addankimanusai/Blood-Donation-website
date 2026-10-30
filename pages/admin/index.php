<?php
/**
 * Admin Dashboard
 * Blood Donation Website
 */

require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blood Bond</title>
    <link rel="stylesheet" href="../../styles.css?v=20260814">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="../../index.html" class="logo" aria-label="Blood Bond home"><span class="brand-mark" aria-hidden="true">+</span><span>BLOOD BOND</span></a>
            <div class="navbar-actions">
                <span style="color:white;">Admin: <?= htmlspecialchars($user['email']) ?></span>
                <a href="../../pages/dashboard.html" class="btn">Dashboard</a>
                <a href="#" class="btn btn-danger" onclick="logoutAdmin(event)">Logout</a>
            </div>
        </nav>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <h2>Admin Dashboard</h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="number" id="totalDonors">-</div>
                <div>Total Donors</div>
            </div>
            <div class="stat-card">
                <div class="number" id="totalAppointments">-</div>
                <div>Appointments</div>
            </div>
            <div class="stat-card">
                <div class="number" id="lowStock">-</div>
                <div>Low Stock Types</div>
            </div>
        </div>

        <div class="admin-card">
            <h3>Management</h3>
            <div class="admin-links">
                <a href="donors.php" class="btn">Manage Donors</a>
                <a href="appointments.php" class="btn">Manage Appointments</a>
                <a href="inventory.php" class="btn">Manage Inventory</a>
                <a href="export.php" class="btn">Export Data</a>
            </div>
        </div>
    </div>

    <script src="../../script.js"></script>
    <script>
    async function loadStats() {
        try {
            const [donorsRes, apptsRes, invRes] = await Promise.all([
                fetch('../../api/donors.php'),
                fetch('../../api/appointments.php'),
                fetch('../../api/inventory.php')
            ]);

            const donors = await donorsRes.json();
            const appts = await apptsRes.json();
            const inv = await invRes.json();

            document.getElementById('totalDonors').textContent = donors.success ? donors.data.pagination.total : '-';
            document.getElementById('totalAppointments').textContent = appts.success ? appts.data.length : '-';

            if (inv.success) {
                const lowStock = inv.data.filter(i => i.units < 10).length;
                document.getElementById('lowStock').textContent = lowStock;
            }
        } catch (error) {
            console.error('Failed to load stats', error);
        }
    }

    loadStats();

    async function logoutAdmin(event) {
        event.preventDefault();

        try {
            await fetch('../../api/logout.php', { method: 'POST' });
        } finally {
            localStorage.removeItem('user');
            window.location.href = '../login.html';
        }
    }
    </script>
</body>
</html>
