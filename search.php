<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$search_results = null;
$search_query = '';
$filter_type = '';
$filter_category = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['q']) || isset($_GET['type']) || isset($_GET['category']))) {
    $search_query = $_GET['q'] ?? '';
    $filter_type = $_GET['type'] ?? '';
    $filter_category = $_GET['category'] ?? '';

    $sql = "SELECT i.*, u.full_name FROM items i JOIN users u ON i.user_id = u.id WHERE status='open'";

    if (!empty($search_query)) {
        $search_query_escaped = $db->real_escape_string($search_query);
        $sql .= " AND (i.item_name LIKE '%$search_query_escaped%' OR i.description LIKE '%$search_query_escaped%')";
    }

    if (!empty($filter_type)) {
        $filter_type_escaped = $db->real_escape_string($filter_type);
        $sql .= " AND i.item_type = '$filter_type_escaped'";
    }

    if (!empty($filter_category)) {
        $filter_category_escaped = $db->real_escape_string($filter_category);
        $sql .= " AND i.category = '$filter_category_escaped'";
    }

    $sql .= " ORDER BY i.created_at DESC";
    $search_results = $db->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Items - Campus Lost & Found</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/config/navbar.php'; ?>

    <div class="search-container">
        <h1>Search Items</h1>

        <form method="GET" class="search-form">
            <div class="form-row">
                <input type="text" name="q" placeholder="Search item name or description..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>

            <div class="filters">
                <select name="type">
                    <option value="">All Types</option>
                    <option value="lost" <?php echo $filter_type === 'lost' ? 'selected' : ''; ?>>Lost</option>
                    <option value="found" <?php echo $filter_type === 'found' ? 'selected' : ''; ?>>Found</option>
                </select>

                <select name="category">
                    <option value="">All Categories</option>
                    <option value="electronics" <?php echo $filter_category === 'electronics' ? 'selected' : ''; ?>>Electronics</option>
                    <option value="clothing" <?php echo $filter_category === 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                    <option value="documents" <?php echo $filter_category === 'documents' ? 'selected' : ''; ?>>Documents</option>
                    <option value="accessories" <?php echo $filter_category === 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                    <option value="keys" <?php echo $filter_category === 'keys' ? 'selected' : ''; ?>>Keys</option>
                    <option value="other" <?php echo $filter_category === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>

                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>

        <div class="results-section">
            <?php if ($search_results !== null): ?>
                <?php if ($search_results->num_rows > 0): ?>
                    <p class="results-count">Found <?php echo $search_results->num_rows; ?> item(s)</p>
                    <div class="items-grid">
                        <?php while ($item = $search_results->fetch_assoc()): ?>
                            <div class="item-card">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Item">
                                <?php else: ?>
                                    <div class="item-placeholder">No Image</div>
                                <?php endif; ?>
                                <div class="item-content">
                                    <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                    <p class="item-type">
                                        <span class="badge badge-<?php echo $item['item_type']; ?>">
                                            <?php echo strtoupper($item['item_type']); ?>
                                        </span>
                                    </p>
                                    <p class="item-category"><?php echo ucfirst(htmlspecialchars($item['category'])); ?></p>
                                    <p class="item-location">📍 <?php echo htmlspecialchars($item['location']); ?></p>
                                    <p class="item-meta">
                                        <small><?php echo $item['date_reported']; ?></small>
                                    </p>
                                    <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <p>No items found matching your search criteria.</p>
                        <p>Try different keywords or filters.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="search-help">
                    <p>Use the search form above to find lost or found items.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/config/footer.php'; ?>
</body>
</html>