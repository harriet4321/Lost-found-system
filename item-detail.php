<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$item_id = $_GET['id'] ?? 0;

if (!$item_id) {
    header('Location: search.php');
    exit;
}

$stmt = $db->prepare("SELECT i.*, u.full_name, u.email, u.phone FROM items i JOIN users u ON i.user_id = u.id WHERE i.id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: search.php');
    exit;
}

$item = $result->fetch_assoc();

$matches_stmt = $db->prepare("
    SELECT i.*, u.full_name FROM items i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.category = ? AND i.item_type != ? AND i.status = 'open' AND i.id != ?
    LIMIT 5
");
$matches_stmt->bind_param("ssi", $item['category'], $item['item_type'], $item_id);
$matches_stmt->execute();
$matches_result = $matches_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['item_name']); ?> - Campus Lost & Found</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/config/navbar.php';  ?>

    <div class="detail-container">
        <div class="detail-grid">
            <div class="detail-image">
                <?php if ($item['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                <?php else: ?>
                    <div class="item-placeholder-large">No Image Available</div>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <h1><?php echo htmlspecialchars($item['item_name']); ?></h1>
                
                <div class="info-badges">
                    <span class="badge badge-<?php echo $item['item_type']; ?>">
                        <?php echo strtoupper($item['item_type']); ?>
                    </span>
                    <span class="badge"><?php echo ucfirst($item['category']); ?></span>
                </div>

                <div class="info-section">
                    <h3>Description</h3>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                </div>

                <div class="info-section">
                    <h3>Details</h3>
                    <ul>
                        <li><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></li>
                        <li><strong>Date:</strong> <?php echo $item['date_reported']; ?></li>
                        <li><strong>Status:</strong> <?php echo ucfirst($item['status']); ?></li>
                    </ul>
                </div>

                <div class="info-section">
                    <h3>Posted By</h3>
                    <div class="poster-info">
                        <p><strong><?php echo htmlspecialchars($item['full_name']); ?></strong></p>
                        <p>📧 <?php echo htmlspecialchars($item['email']); ?></p>
                        <?php if ($item['phone']): ?>
                            <p>📞 <?php echo htmlspecialchars($item['phone']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isLoggedIn() && $_SESSION['user_id'] !== $item['user_id']): ?>
                    <button class="btn btn-primary" onclick="contactPoster()">Contact Poster</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($matches_result->num_rows > 0): ?>
            <div class="potential-matches">
                <h2>Potential Matches</h2>
                <div class="items-grid">
                    <?php while ($match = $matches_result->fetch_assoc()): ?>
                        <div class="item-card">
                            <?php if ($match['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($match['image_url']); ?>" alt="Item">
                            <?php else: ?>
                                <div class="item-placeholder">No Image</div>
                            <?php endif; ?>
                            <div class="item-content">
                                <h3><?php echo htmlspecialchars($match['item_name']); ?></h3>
                                <p class="item-type">
                                    <span class="badge badge-<?php echo $match['item_type']; ?>">
                                        <?php echo strtoupper($match['item_type']); ?>
                                    </span>
                                </p>
                                <a href="item-detail.php?id=<?php echo $match['id']; ?>" class="btn btn-small">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function contactPoster() {
            const email = "<?php echo htmlspecialchars($item['email']); ?>";
            const itemName = "<?php echo htmlspecialchars($item['item_name']); ?>";
            const mailtoLink = `mailto:${email}?subject=Regarding: ${itemName}&body=Hi,\n\nI'm interested in your post about ${itemName}.\n\nPlease get in touch.`;
            window.location.href = mailtoLink;
        }
    </script>

    <?php include __DIR__ . '/config/footer.php'; ?>
</body>
</html>