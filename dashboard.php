<?php
/**
 * OFTK – Member dashboard (protected)
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

oftk_require_login();
$user = oftk_current_user();
$pageTitle = 'Paneli i Anëtarit';
$layoutMode = 'dashboard';
$currentUser = $user;
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | OFTK</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php require __DIR__ . '/includes/header.php'; ?>
  <main>
    <section class="page-hero">
      <div class="container">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p>Mirë se erdhet, <?= htmlspecialchars($user['full_name'] ?: $user['email']) ?>.</p>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="news-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
          <a href="dokumente.html" class="card">
            <div class="card-body">
              <h3 class="card-title">Dokumente</h3>
              <p>Shkarkoni formularët dhe dokumentet zyrtare.</p>
            </div>
          </a>
          <a href="kongresi.html" class="card">
            <div class="card-body">
              <h3 class="card-title">Kongresi / Konferenca</h3>
              <p>Informacion për Konferencën Ndërkombëtare të Fizioterapisë.</p>
            </div>
          </a>
          <a href="lajme.html" class="card">
            <div class="card-body">
              <h3 class="card-title">Lajmet</h3>
              <p>Lexoni njoftimet e fundit nga OFTK.</p>
            </div>
          </a>
          <a href="kontakt.html" class="card">
            <div class="card-body">
              <h3 class="card-title">Kontakt</h3>
              <p>Na kontaktoni për pyetje ose ankesa.</p>
            </div>
          </a>
        </div>
        <?php if (($user['role'] ?? '') === 'admin'): ?>
          <a href="admin/index.php" class="card" style="grid-column: 1 / -1; background: var(--primary); color: var(--white);">
            <div class="card-body">
              <h3 class="card-title">Paneli Admin – Menaxho përmbajtjen</h3>
              <p>Shtoni ose fshini lajme, dokumente, trajnime, foto, konkurse.</p>
            </div>
          </a>
        <?php endif; ?>
        <div class="contact-info-card mt-2" style="max-width: 560px;">
          <h3>Informacion llogarie</h3>
          <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
          <p><strong>Roli:</strong> <?= htmlspecialchars($user['role'] === 'admin' ? 'Administrator' : 'Anëtar') ?></p>
        </div>
      </div>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
  <script src="js/main.js"></script>
</body>
</html>
