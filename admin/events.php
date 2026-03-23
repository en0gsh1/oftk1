<?php
/**
 * OFTK – Admin: manage events / training
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';

oftk_require_admin();

$items = oftk_get_events();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (string) $_POST['delete_id'];
        $items = array_values(array_filter($items, fn($x) => ($x['id'] ?? '') !== $id));
        oftk_save_events($items);
        $message = 'Eventi u fshi.';
    } elseif (!empty($_POST['title'])) {
        $items[] = [
            'id' => oftk_next_id($items),
            'day' => trim((string) ($_POST['day'] ?? '1')),
            'month' => trim((string) ($_POST['month'] ?? '')),
            'title' => trim((string) $_POST['title']),
            'meta' => trim((string) ($_POST['meta'] ?? '')),
            'body' => trim((string) ($_POST['body'] ?? '')),
            'link' => trim((string) ($_POST['link'] ?? 'kontakt.html')),
        ];
        oftk_save_events($items);
        $message = 'Eventi u shtua.';
    }
    $items = oftk_get_events();
}

$pageTitle = 'Menaxho Trajnime & Evente';
$backLink = 'index.php';
require __DIR__ . '/_admin_layout.php';

if (empty($items)) {
    echo '<p class="card-meta">Nuk ka asnjë event.</p>';
}
foreach ($items as $item):
?>
  <div class="admin-item">
    <div>
      <strong><?= htmlspecialchars($item['title'] ?? '') ?></strong>
      <span class="card-meta"><?= htmlspecialchars(($item['day'] ?? '') . ' ' . ($item['month'] ?? '')) ?></span>
    </div>
    <form method="post" style="display:inline;" onsubmit="return confirm('Fshi këtë event?');">
      <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
      <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.75rem;">Fshi</button>
    </form>
  </div>
<?php endforeach; ?>

<h3 class="mt-2">Shto event / trajnim</h3>
<form method="post" class="admin-form">
  <div class="form-row">
    <div class="form-group">
      <label>Dita (numër)</label>
      <input type="text" name="day" class="form-control" placeholder="15">
    </div>
    <div class="form-group">
      <label>Muaji (p.sh. Mars 2025)</label>
      <input type="text" name="month" class="form-control">
    </div>
  </div>
  <div class="form-group">
    <label>Titulli</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Meta (vend · kohë)</label>
    <input type="text" name="meta" class="form-control" placeholder="Prishtinë · 09:00 – 17:00">
  </div>
  <div class="form-group">
    <label>Përshkrimi</label>
    <textarea name="body" class="form-control" rows="2"></textarea>
  </div>
  <div class="form-group">
    <label>Lidhje (p.sh. kontakt.html)</label>
    <input type="text" name="link" class="form-control" value="kontakt.html">
  </div>
  <button type="submit" class="btn btn-primary">Shto event</button>
</form>
<?php require __DIR__ . '/_admin_footer.php'; ?>
