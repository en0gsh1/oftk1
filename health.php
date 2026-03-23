<?php
/**
 * OFTK – Health check: PHP and user system readiness.
 * Open in browser (e.g. http://localhost:8000/health.php)
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
$dataWritable = is_dir($dataDir)
    ? is_writable($dataDir)
    : (is_writable(__DIR__) && @mkdir($dataDir, 0755, true));

$usersCount = 0;
try {
    define('OFTK_APP', true);
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/includes/db.php';
    $usersCount = count(oftk_get_users());
} catch (Throwable $e) {
    $usersError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <title>OFTK – Kontroll</title>
  <style>
    body { font-family: system-ui, sans-serif; padding: 2rem; max-width: 560px; }
    code { background: #f1f5f9; padding: 0.2em 0.4em; border-radius: 4px; }
    .ok { color: #059669; }
    .warn { background: #fff7ed; padding: 0.5rem; border-radius: 6px; }
  </style>
</head>
<body>
  <h1>OFTK – Kontroll</h1>
  <p><strong>PHP punon.</strong> Version: <?= htmlspecialchars(phpversion()) ?></p>
  <p>Dosja <code>data/</code>: <?= $dataWritable ? '<span class="ok">e shkrueshme ✓</span>' : 'nuk mund të shkruhet ✗' ?></p>
  <?php if (isset($usersError)): ?>
    <p>Përdoruesit: <span style="color: #dc2626;">gabim – <?= htmlspecialchars($usersError) ?></span></p>
  <?php else: ?>
    <p>Përdorues të regjistruar: <strong><?= $usersCount ?></strong></p>
  <?php endif; ?>
  <?php if ($usersCount === 0): ?>
    <p class="warn">→ Së pari <a href="create-admin.php">hapni create-admin.php</a> për të krijuar llogarinë administrator (admin@oftk-ks.org).</p>
  <?php else: ?>
    <p>→ <a href="login.php">Hyr në llogari</a></p>
  <?php endif; ?>
  <p><small>Përdoruesit ruhen në data/users.php (pa bazë të dhënash).</small></p>
</body>
</html>
