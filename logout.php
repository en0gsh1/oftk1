<?php
/**
 * OFTK – Logout: destroy session and redirect to home
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

oftk_logout();
header('Location: index.html');
exit;
