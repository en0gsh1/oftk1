<?php
/**
 * OFTK – Admin: manage news
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';

oftk_require_admin();

$items = oftk_get_news();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (string) $_POST['delete_id'];
        $items = array_values(array_filter($items, fn($x) => ($x['id'] ?? '') !== $id));
        oftk_save_news($items);
        $message = 'Lajmi u fshi.';
    } elseif (!empty($_POST['title'])) {
        $items[] = [
            'id' => oftk_next_id($items),
            'title' => trim((string) $_POST['title']),
            'date' => trim((string) ($_POST['date'] ?? date('d F Y'))),
            'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
            'image' => trim((string) ($_POST['image'] ?? '')),
            'url' => trim((string) ($_POST['url'] ?? 'lajme.html')),
        ];
        oftk_save_news($items);
        $message = 'Lajmi u shtua.';
    }
    $items = oftk_get_news();
}

$pageTitle = 'Menaxho Lajmet';
$backLink = 'index.php';
require __DIR__ . '/_admin_layout.php';

if (empty($items)) {
    echo '<p class="card-meta">Nuk ka asnjë lajm.</p>';
}
foreach ($items as $item):
?>
  <div class="admin-item">
    <div>
      <strong><?= htmlspecialchars($item['title'] ?? '') ?></strong>
      <span class="card-meta"><?= htmlspecialchars($item['date'] ?? '') ?></span>
    </div>
    <form method="post" style="display:inline;" onsubmit="return confirm('Fshi këtë lajm?');">
      <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
      <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.75rem;">Fshi</button>
    </form>
  </div>
<?php endforeach; ?>

<h3 class="mt-2">Shto lajm</h3>
<form method="post" class="admin-form">
  <div class="form-group">
    <label>Titulli</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Data (p.sh. 21 Shkurt 2025)</label>
    <input type="text" name="date" class="form-control" value="<?= date('d F Y') ?>">
  </div>
  <div class="form-group">
    <label>Përmbledhje</label>
    <textarea name="excerpt" class="form-control" rows="2"></textarea>
  </div>
  <div class="form-group">
    <label>URL imazhi</label>
    <input type="url" name="image" class="form-control" placeholder="https://...">
  </div>
  <div class="form-group">
    <label>Lidhje (url)</label>
    <input type="text" name="url" class="form-control" value="lajme.html" placeholder="lajme.html">
    <small class="form-hint">Lajmi i fundit shfaqet në ballinë (hero) dhe në faqen Lajmet. Përdoret i njëjti skedar data/news.json.</small>
  </div>
  <button type="submit" class="btn btn-primary">Shto lajm</button>
</form>
<?php require __DIR__ . '/_admin_footer.php'; ?>
