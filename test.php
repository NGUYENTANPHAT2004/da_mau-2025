<?php
// Test file để kiểm tra hệ thống
require_once 'bootstrap.php';

echo "<h1>Test Ecommerce MVC System</h1>";
echo "<p>✅ Bootstrap loaded successfully!</p>";
echo "<p>✅ Session started: " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "</p>";
echo "<p>✅ BASE_URL: " . BASE_URL . "</p>";
echo "<p>✅ SITE_NAME: " . SITE_NAME . "</p>";

// Test database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<p>✅ Database connection: Success</p>";
} catch(Exception $e) {
    echo "<p>❌ Database connection: Failed - " . $e->getMessage() . "</p>";
}

// Test helper functions
echo "<p>✅ Helper functions loaded:</p>";
echo "<ul>";
echo "<li>getStatusBadge('pending'): " . getStatusBadge('pending') . "</li>";
echo "<li>getStatusText('pending'): " . getStatusText('pending') . "</li>";
echo "<li>formatPrice(1000000): " . formatPrice(1000000) . "</li>";
echo "<li>isLoggedIn(): " . (isLoggedIn() ? 'Yes' : 'No') . "</li>";
echo "</ul>";

echo "<p><strong>System is ready! 🎉</strong></p>";
?> 