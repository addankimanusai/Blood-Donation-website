<?php
/**
 * Admin - Manage Inventory
 */

require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$conn = getDBConnection();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "UPDATE blood_inventory SET units_available = ? WHERE blood_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $data['units'], $data['blood_type']);
    $stmt->execute();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Updated']);
    exit;
}

$sql = "SELECT * FROM blood_inventory ORDER BY blood_type";
$result = $conn->query($sql);
$inventory = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory - Blood Bond</title>
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
        <h2>Manage Blood Inventory</h2>

        <form id="inventoryForm" class="inventory-form">
            <?php foreach ($inventory as $item): ?>
            <div class="inventory-item <?= $item['units_available'] < 10 ? 'low' : '' ?>">
                <h3><?= $item['blood_type'] ?></h3>
                <div class="units"><?= $item['units_available'] ?> units</div>
                <input type="number" name="units_<?= $item['blood_type'] ?>" value="<?= $item['units_available'] ?>" min="0">
                <input type="hidden" name="blood_type" value="<?= $item['blood_type'] ?>">
                <button type="button" class="btn-small" onclick="updateItem('<?= $item['blood_type'] ?>')">Update</button>
            </div>
            <?php endforeach; ?>
        </form>
    </div>

    <script>
    async function updateItem(bloodType) {
        const input = document.querySelector(`input[name="units_${bloodType}"]`);
        const units = input.value;

        try {
            const response = await fetch('inventory.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ blood_type: bloodType, units: parseInt(units) })
            });

            const result = await response.json();
            if (result.success) {
                alert('Updated successfully');
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
