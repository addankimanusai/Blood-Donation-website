<?php
/**
 * Admin - Manage Donors
 */

require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$conn = getDBConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Count total
$countSql = "SELECT COUNT(*) FROM donors WHERE is_active = TRUE";
$params = [];
if ($search) {
    $countSql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
}
$stmt = $conn->prepare($countSql);
if ($params) $stmt->bind_param("sss", ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_row()[0];

$pagination = paginate($total, $page, RESULTS_PER_PAGE);

// Fetch donors
$sql = "SELECT * FROM donors WHERE is_active = TRUE";
if ($search) $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($search) {
    $params[] = $pagination['per_page'];
    $params[] = $pagination['offset'];
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $pagination['per_page'], $pagination['offset']);
} else {
    $stmt->bind_param("ii", $pagination['per_page'], $pagination['offset']);
}
$stmt->execute();
$result = $stmt->get_result();
$donors = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donors - Blood Bond</title>
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
        <h2>Manage Donors</h2>

        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search by name, phone, or email" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
            <a href="donors.php" class="btn" style="background:var(--text-secondary);">Clear</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Phone</th>
                    <th>Blood Group</th>
                    <th>State</th>
                    <th>City</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($donors)): ?>
                <tr><td colspan="8" style="text-align:center;">No donors found</td></tr>
                <?php else: ?>
                <?php foreach ($donors as $donor): ?>
                <tr>
                    <td><?= $donor['id'] ?></td>
                    <td><?= htmlspecialchars($donor['name']) ?></td>
                    <td><?= $donor['age'] ?></td>
                    <td><?= htmlspecialchars($donor['phone']) ?></td>
                    <td><?= $donor['blood_group'] ?></td>
                    <td><?= htmlspecialchars($donor['state']) ?></td>
                    <td><?= htmlspecialchars($donor['city']) ?></td>
                    <td><?= formatDate($donor['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_prev']): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="btn">Previous</a>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $pagination['total_pages'] ?></span>
            <?php if ($pagination['has_next']): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="btn">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
