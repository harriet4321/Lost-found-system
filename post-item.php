<?php
// post-item.php

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/session.php";

checkLogin();

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $item_type = $_POST['item_type'] ?? '';
    $location = $_POST['location'] ?? '';
    $date_reported = $_POST['date_reported'] ?? date('Y-m-d');

    if (empty($item_name) || empty($description) || empty($category) || empty($item_type) || empty($location)) {
        $error = 'All fields are required!';
    } else {
        $image_url = null;

        // Handle image upload
        if (isset($_FILES['item_image']) && $_FILES['item_image']['size'] > 0) {
            if ($_FILES['item_image']['size'] > MAX_FILE_SIZE) {
                $error = 'Image size must not exceed 5MB!';
            } else {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = basename($_FILES['item_image']['name']);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    $error = 'Only JPG, PNG, and GIF files are allowed!';
                } else {
                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }

                    $new_filename = uniqid() . '.' . $ext;
                    $upload_path = UPLOAD_DIR . $new_filename;

                    if (move_uploaded_file($_FILES['item_image']['tmp_name'], $upload_path)) {
                        $image_url = 'uploads/' . $new_filename;
                    }
                }
            }
        }

        if (empty($error)) {
            $stmt = $db->prepare(
                "INSERT INTO items (user_id, item_name, description, category, item_type, location, date_reported, image_url) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isssssss", $user_id, $item_name, $description, $category, $item_type, $location, $date_reported, $image_url);

            if ($stmt->execute()) {
                $item_id = $stmt->insert_id;
                
                // Check for automatic matches
                checkAndCreateMatches($item_id, $db);
                
                $success = 'Item posted successfully! <a href="search.php">Search for matches</a>';
            } else {
                $error = 'Failed to post item. Please try again.';
            }
        }
    }
}

function checkAndCreateMatches($item_id, $db) {
    $item = $db->query("SELECT * FROM items WHERE id=$item_id")->fetch_assoc();
    
    if ($item['item_type'] === 'lost') {
        $matching_items = $db->query("
            SELECT * FROM items 
            WHERE item_type='found' 
            AND category = '{$item['category']}' 
            AND status='open' 
            AND id != $item_id
        ");
    } else {
        $matching_items = $db->query("
            SELECT * FROM items 
            WHERE item_type='lost' 
            AND category = '{$item['category']}' 
            AND status='open' 
            AND id != $item_id
        ");
    }

    while ($match = $matching_items->fetch_assoc()) {
        $lost_id = $item['item_type'] === 'lost' ? $item_id : $match['id'];
        $found_id = $item['item_type'] === 'found' ? $item_id : $match['id'];

        $db->query("INSERT INTO matches (lost_item_id, found_item_id) VALUES ($lost_id, $found_id)");
        
        // Notify the user
        $match_id = $db->insert_id;
        $db->query("
            INSERT INTO notifications (user_id, match_id, message) 
            VALUES ({$match['user_id']}, $match_id, 'A potential match was found!')
        ");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Item - Campus Lost & Found</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/config/navbar.php'; ?>

    <div class="form-container">
        <h1>Report Item</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data" class="item-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="item_type">Item Type *</label>
                        <select id="item_type" name="item_type" required>
                            <option value="">Select...</option>
                            <option value="lost">Lost</option>
                            <option value="found">Found</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="item_name">Item Name *</label>
                        <input type="text" id="item_name" name="item_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select...</option>
                            <option value="electronics">Electronics</option>
                            <option value="clothing">Clothing</option>
                            <option value="documents">Documents</option>
                            <option value="accessories">Accessories</option>
                            <option value="keys">Keys</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_reported">Date *</label>
                        <input type="date" id="date_reported" name="date_reported" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" placeholder="e.g., Library, Building A, Parking Lot" required>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe the item in detail..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="item_image">Item Image</label>
                    <input type="file" id="item_image" name="item_image" accept="image/*">
                    <small>Max 5MB. Formats: JPG, PNG, GIF</small>
                </div>

                <button type="submit" class="btn btn-primary">Post Item</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/config/footer.php';
 ?>
</body>
</html>