<?php
/**
 * OFTK – User storage (file-based, no database driver needed)
 */

declare(strict_types=1);

if (!defined('OFTK_APP')) {
    die('Direct access not permitted.');
}

/**
 * Path to users file.
 */
function oftk_users_path(): string {
    return OFTK_USERS_FILE;
}

/**
 * Get all users (array of id, email, password_hash, full_name, role).
 */
function oftk_get_users(): array {
    $path = oftk_users_path();
    if (!is_file($path)) {
        return [];
    }
    $data = @include $path;
    return is_array($data) ? $data : [];
}

/**
 * Find user by email (active only).
 */
function oftk_user_by_email(string $email): ?array {
    $email = trim(strtolower($email));
    foreach (oftk_get_users() as $user) {
        if (isset($user['email']) && strtolower((string) $user['email']) === $email) {
            return [
                'id' => (int) ($user['id'] ?? 0),
                'email' => (string) $user['email'],
                'password_hash' => (string) ($user['password_hash'] ?? ''),
                'full_name' => (string) ($user['full_name'] ?? ''),
                'role' => (string) ($user['role'] ?? 'member'),
            ];
        }
    }
    return null;
}

/**
 * Record login attempt (no-op for file storage; kept for compatibility).
 */
function oftk_record_login_attempt(string $email, string $ip, bool $success): void {
    // Optional: could write to data/attempts.json for rate limiting
}

/**
 * Count recent failed attempts (always 0 for file storage; no lockout).
 */
function oftk_failed_attempts_count(string $email, int $withinMinutes = 15): int {
    return 0;
}
