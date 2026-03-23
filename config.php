<?php
/**
 * OFTK – Oda e Fizioterapeutëve të Kosovës
 * Application configuration
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('OFTK_APP')) {
    define('OFTK_APP', true);
}

// Base path (parent of document root if needed)
$baseDir = __DIR__;
define('OFTK_BASE', $baseDir);

// Users file (no database – works without PDO SQLite driver)
define('OFTK_USERS_FILE', $baseDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'users.php');

// Content JSON files (admin-managed)
define('OFTK_DATA_DIR', $baseDir . DIRECTORY_SEPARATOR . 'data');
define('OFTK_NEWS_FILE', OFTK_DATA_DIR . DIRECTORY_SEPARATOR . 'news.json');
define('OFTK_DOCUMENTS_FILE', OFTK_DATA_DIR . DIRECTORY_SEPARATOR . 'documents.json');
define('OFTK_EVENTS_FILE', OFTK_DATA_DIR . DIRECTORY_SEPARATOR . 'events.json');
define('OFTK_GALLERY_FILE', OFTK_DATA_DIR . DIRECTORY_SEPARATOR . 'gallery.json');
define('OFTK_COMPETITIONS_FILE', OFTK_DATA_DIR . DIRECTORY_SEPARATOR . 'competitions.json');
define('OFTK_PHYSIOTHERAPISTS_FILE', OFTK_DATA_DIR . DIRECTORY_SEPARATOR . 'physiotherapists.json');

// Session name (unique to avoid conflicts)
define('OFTK_SESSION_NAME', 'OFTK_SESSION');

// Security: session cookie settings
define('OFTK_SESSION_LIFETIME', 60 * 60 * 8); // 8 hours
define('OFTK_SESSION_IDLE', 60 * 30);         // 30 min max idle (regenerate)

// Login attempt limits (basic rate limiting)
define('OFTK_MAX_LOGIN_ATTEMPTS', 5);
define('OFTK_LOCKOUT_MINUTES', 15);

// Site URL base (for redirects) – no trailing slash
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
define('OFTK_URL_BASE', rtrim($protocol . '://' . $host . $script, '/'));

// Helper: full URL to a path
function oftk_url(string $path = ''): string {
    $path = ltrim($path, '/');
    return OFTK_URL_BASE . ($path ? '/' . $path : '');
}
