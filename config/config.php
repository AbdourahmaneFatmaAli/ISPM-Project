<?php
/**
 * Global configuration and security utilities.
 */

// Define the root file system path
define('ROOT_PATH', dirname(__DIR__));

// Load environment variables
require_once ROOT_PATH . '/config/env_loader.php';

// Define BASE_URL - Automatically detect if in a subdirectory
if (isset($_ENV['BASE_URL'])) {
    $base_url = $_ENV['BASE_URL'];
} else {
    // Auto-detect subdirectory (e.g., /ISPM-Project-main/)
    $script_name = $_SERVER['SCRIPT_NAME']; // e.g. /ISPM-Project-main/index.php
    $base_dir = str_replace('\\', '/', dirname($script_name));
    $base_url = ($base_dir === '/') ? '/' : $base_dir . '/';
}

if (substr($base_url, -1) !== '/') {
    $base_url .= '/';
}
define('BASE_URL', $base_url);

/**
 * CSRF Protection Functions
 */
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * HTML Escaping Utility
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
