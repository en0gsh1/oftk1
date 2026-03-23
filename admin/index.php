<?php
/**
 * OFTK – Admin panel home (admin only)
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';

oftk_require_admin();

$physioCount = count(oftk_get_physiotherapists());
$user = oftk_current_user();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Paneli Admin | OFTK</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <a href="index.php" class="logo">OFTK Admin</a>
      <div class="header-actions">
        <a href="../dashboard.php">Paneli i Anëtarit</a>
        <a href="../index.html">Faqja kryesore</a>
        <span><?= htmlspecialchars($user['email']) ?></span>
        <a href="../logout.php" class="btn btn-outline">Dil</a>
      </div>
    </div>
  </header>
  <main class="section">
    <div class="container">
      <h1>Paneli Admin</h1>
      <p style="color: var(--gray-500); margin-bottom: 2rem;">Shtoni, ndryshoni ose fshini përmbajtje nga faqet publike.</p>
      <div class="news-grid" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));">
        <a href="news.php" class="card">
          <div class="card-body">
            <h3 class="card-title">Lajmet</h3>
            <p><?= count(oftk_get_news()) ?> lajme</p>
          </div>
        </a>
        <a href="documents.php" class="card">
          <div class="card-body">
            <h3 class="card-title">Dokumente</h3>
            <p><?= count(oftk_get_documents()) ?> dokumente</p>
          </div>
        </a>
        <a href="events.php" class="card">
          <div class="card-body">
            <h3 class="card-title">Trajnime & Evente</h3>
            <p><?= count(oftk_get_events()) ?> evente</p>
          </div>
        </a>
        <a href="gallery.php" class="card">
          <div class="card-body">
            <h3 class="card-title">Foto Galeria</h3>
            <p><?= count(oftk_get_gallery()) ?> foto</p>
          </div>
        </a>
        <a href="competitions.php" class="card">
          <div class="card-body">
            <h3 class="card-title">Konkurset</h3>
            <p><?= count(oftk_get_competitions()) ?> shpallje</p>
          </div>
        </a>
        <a href="physiotherapists.php" class="card">
          <div class="card-body">
            <h3 class="card-title">Fizioterapeutët</h3>
            <p><?= $physioCount ?> në regjistër</p>
          </div>
        </a>
      </div>
    </div>
  </main>
</body>
</html>
