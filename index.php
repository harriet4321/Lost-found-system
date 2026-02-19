<?php
// index.php

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/session.php";

// Get statistics
$lost_count = $db->query("SELECT COUNT(*) as count FROM items WHERE item_type='lost' AND status='open'")->fetch_assoc()['count'];
$found_count = $db->query("SELECT COUNT(*) as count FROM items WHERE item_type='found' AND status='open'")->fetch_assoc()['count'];
$matches_count = $db->query("SELECT COUNT(*) as count FROM matches WHERE status='pending'")->fetch_assoc()['count'];

// Get recent items
$recent_items = $db->query("
    SELECT i.*, u.full_name 
    FROM items i 
    JOIN users u ON i.user_id = u.id 
    ORDER BY i.created_at DESC 
    LIMIT 6
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Lost & Found System</title>
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <?php include __DIR__ . "/config/navbar.php"; ?>

    <div class="hero">
        <div class="hero-content">
            <h1>Campus Lost & Found</h1>
            <p>Help others find their lost items or report found items on campus</p>
            <div class="hero-buttons">
                <?php if (isLoggedIn()): ?>
                    <a href="post-item.php" class="btn btn-primary">Report Item</a>
                    <a href="search.php" class="btn btn-secondary">Search Items</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="stats-section">
        <div class="stat-card">
            <div class="stat-number"><?php echo $lost_count; ?></div>
            <div class="stat-label">Items Reported Lost</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $found_count; ?></div>
            <div class="stat-label">Items Found</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $matches_count; ?></div>
            <div class="stat-label">Pending Matches</div>
        </div>
    </div>

    <section class="recent-items">
        <h2>Recent Items</h2>
        <div class="items-grid">
            <?php while ($item = $recent_items->fetch_assoc()): ?>
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
                        <p class="item-description"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</p>
                        <p class="item-meta">
                            <small>Posted by: <?php echo htmlspecialchars($item['full_name']); ?></small>
                            <small>Location: <?php echo htmlspecialchars($item['location']); ?></small>
                        </p>
                        <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-small">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <?php include __DIR__ . "/config/footer.php"; ?>
</body>
</html>