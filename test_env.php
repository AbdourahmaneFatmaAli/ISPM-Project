<?php
require_once 'config/env_loader.php';

echo "SMTP_USER: " . (isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : "NOT SET") . "\n";
echo "SMTP_HOST: " . (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : "NOT SET") . "\n";
?>
