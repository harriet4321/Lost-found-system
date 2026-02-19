
<?php
// config/config.php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Leave empty if no password
define('DB_NAME', 'lost_found_system');

// Application settings
define('APP_NAME', 'Campus Lost & Found System');
define('APP_URL', 'http://localhost/lost-found');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?> 