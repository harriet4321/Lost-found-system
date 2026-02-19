<?php
// dashboard.php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

checkLogin();

$user_id = $_SESSION['user_id'];

// Get user's items
$my_items = $db->prepare("
    SELECT * FROM items 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$my_items->bind_param("i", $user_id);
$my_items->execute();
$items_result = $my_items->get_result();

// Get notifications
$notifications = $db->prepare("
    SELECT n.*, m.lost_item_id, m.found_item_id, i1.item_name as lost_item, i2.item_name as found_item
    FROM notifications n
    JOIN matches m ON n.match_id = m.id
    JOIN items i1 ON m.lost_item_id = i1.id
    JOIN items i2 ON m.found_item_id = i2.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 5
");
$notifications->bind_param("i", $user_id);
$notifications->execute();
$notifications_result = $notifications->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Campus Lost & Found</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php  include __DIR__ . '/config/navbar.php'; ?>

    <div class="dashboard-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>

        <div class="dashboard-grid">
            <div class="dashboard-section">
                <h2>Your Items</h2>
                <a href="post-item.php" class="btn btn-primary">+ Post New Item</a>
                
                <div class="items-list">
                    <?php if ($items_result->num_rows > 0): ?>
                        <?php while ($item = $items_result->fetch_assoc()): ?>
                            <div class="list-item">
                                <div class="item-info">
                                    <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                    <p><?php echo ucfirst($item['item_type']); ?> • <?php echo $item['date_reported']; ?></p>
                                </div>
                                <div class="item-actions">
                                    <a href="item-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-small">View</a>
                                    <a href="edit-item.php?id=<?php echo $item['id']; ?>" class="btn btn-small">Edit</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No items posted yet. <a href="post-item.php">Post your first item</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Notifications</h2>
                <?php if ($notifications_result->num_rows > 0): ?>
                    <div class="notifications-list">
                        <?php while ($notif = $notifications_result->fetch_assoc()): ?>
                            <div class="notification-item">
                                <p>
                                    <strong><?php echo htmlspecialchars($notif['lost_item']); ?></strong> 
                                    might match 
                                    <strong><?php echo htmlspecialchars($notif['found_item']); ?></strong>
                                </p>
                                <a href="match-detail.php?id=<?php echo $notif['match_id']; ?>" class="btn btn-small">
                                    View Match
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No new notifications. Check back later!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/config/footer.php'; ?>
</body>
</html>