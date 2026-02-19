<?php
require_once __DIR__ . '/session.php';
// includes/navbar.php
?>
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-brand">Campus Lost & Found</a>
        <ul class="navbar-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="search.php">Search</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="post-item.php">Post Item</a></li>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>