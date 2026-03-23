<?php
/**
 * OFTK – Legacy install redirect (no database setup).
 * Redirects to create-admin.php for initial admin account creation.
 */

declare(strict_types=1);

header('Location: create-admin.php', true, 302);
exit;
