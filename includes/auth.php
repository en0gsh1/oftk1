<?php
/**
 * OFTK – Authentication and session handling
 */

declare(strict_types=1);

if (!defined('OFTK_APP')) {
    die('Direct access not permitted.');
}

/**
 * Start secure session if not already started.
 */
function oftk_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name(OFTK_SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Regenerate ID periodically to reduce fixation risk
    $reg = $_SESSION['_regenerated_at'] ?? 0;
    if (time() - $reg > OFTK_SESSION_IDLE) {
        session_regenerate_id(true);
        $_SESSION['_regenerated_at'] = time();
    }
}

/**
 * Check if current user is logged in.
 */
function oftk_is_logged_in(): bool {
    oftk_session_start();
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_email']);
}

/**
 * Get current user array (id, email, full_name, role) or null.
 */
function oftk_current_user(): ?array {
    oftk_session_start();
    if (!oftk_is_logged_in()) {
        return null;
    }
    return [
        'id'        => (int) $_SESSION['user_id'],
        'email'     => (string) $_SESSION['user_email'],
        'full_name' => (string) ($_SESSION['user_name'] ?? ''),
        'role'      => (string) ($_SESSION['user_role'] ?? 'member'),
    ];
}

/**
 * Require login; redirect to login page if not authenticated.
 */
function oftk_require_login(): void {
    oftk_session_start();
    if (!oftk_is_logged_in()) {
        $return = urlencode($_SERVER['REQUEST_URI'] ?? 'dashboard.php');
        header('Location: login.php?return=' . $return);
        exit;
    }
}

/**
 * Require admin role; redirect to dashboard if not admin.
 */
function oftk_require_admin(): void {
    oftk_require_login();
    $user = oftk_current_user();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Log in user (set session, regenerate ID).
 */
function oftk_login_user(array $user): void {
    oftk_session_start();
    session_regenerate_id(true);
    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name']  = $user['full_name'] ?? '';
    $_SESSION['user_role']  = $user['role'] ?? 'member';
    $_SESSION['_regenerated_at'] = time();
}

/**
 * Log out and redirect.
 */
function oftk_logout(): void {
    oftk_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Generate CSRF token for current session.
 */
function oftk_csrf_token(): string {
    oftk_session_start();
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Validate CSRF token (constant-time).
 */
function oftk_csrf_verify(string $token): bool {
    oftk_session_start();
    $expected = $_SESSION['_csrf_token'] ?? '';
    return $expected !== '' && hash_equals($expected, $token);
}

/**
 * Get client IP (best effort).
 */
function oftk_client_ip(): string {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $v = trim((string) $_SERVER[$k]);
            if (strpos($v, ',') !== false) {
                $v = trim(explode(',', $v)[0]);
            }
            if (filter_var($v, FILTER_VALIDATE_IP)) {
                return $v;
            }
        }
    }
    return '0.0.0.0';
}
