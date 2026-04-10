<?php
require_once 'includes/auth_check.php';
require_role('admin'); // Only admins can run migrations
require_once 'config/database.php';

echo "<h2>Database Migration</h2>";

try {
    $pdo->exec("ALTER TABLE appointments MODIFY status ENUM('booked', 'checked-in', 'completed', 'missed') DEFAULT 'booked'");
    echo "<p style='color: green;'>✅ Appointments table updated (missed status added).</p>";

    $pdo->exec("ALTER TABLE queue MODIFY status ENUM('waiting', 'serving', 'done', 'missed') DEFAULT 'waiting'");
    echo "<p style='color: green;'>✅ Queue table updated (missed status added).</p>";

    // Check if updated_at exists to avoid error
    $check = $pdo->query("SHOW COLUMNS FROM queue LIKE 'updated_at'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE queue ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✅ Queue updated_at column added.</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Queue updated_at column already exists.</p>";
    }

    echo "<h3>Migration Complete!</h3>";
    echo "<a href='admin_dashboard.php'>Go to Admin Dashboard</a>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Migration failed: " . $e->getMessage() . "</p>";
}
?>
