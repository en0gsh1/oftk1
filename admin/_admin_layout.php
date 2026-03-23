<?php
/**
 * OFTK – Admin panel layout (top + title + optional message)
 * Expects: $pageTitle, $backLink (optional), $message (optional)
 */
if (!isset($backLink)) $backLink = 'index.php';
$message = $message ?? '';
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> | OFTK</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    .admin-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      background: var(--gray-50);
      border-radius: var(--radius);
      margin-bottom: 0.5rem;
      border: 1px solid var(--gray-200);
    }
    .admin-form { max-width: 560px; margin-top: 1rem; }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <a href="<?= htmlspecialchars($backLink) ?>" class="logo">← <?= htmlspecialchars($pageTitle ?? 'Admin') ?></a>
      <div class="header-actions">
        <a href="index.php">Paneli Admin</a>
        <a href="../dashboard.php">Paneli</a>
        <a href="../index.html">Faqja</a>
        <a href="../logout.php" class="btn btn-outline">Dil</a>
      </div>
    </div>
  </header>
  <main class="section">
    <div class="container">
      <h1><?= htmlspecialchars($pageTitle ?? 'Admin') ?></h1>
      <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <h3 style="margin-top: 1.5rem;"><?= htmlspecialchars($adminListHeading ?? 'Lista ekzistuese') ?></h3>
