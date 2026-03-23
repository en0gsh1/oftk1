<?php
/**
 * OFTK – Admin: manage competitions
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';

oftk_require_admin();

$items = oftk_get_competitions();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (string) $_POST['delete_id'];
        $items = array_values(array_filter($items, fn($x) => ($x['id'] ?? '') !== $id));
        oftk_save_competitions($items);
        $message = 'Shpallja u fshi.';
    } elseif (!empty($_POST['title'])) {
        $items[] = [
            'id' => oftk_next_id($items),
            'title' => trim((string) $_POST['title']),
            'date' => trim((string) ($_POST['date'] ?? '')),
            'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
            'link' => trim((string) ($_POST['link'] ?? 'kontakt.html')),
        ];
        oftk_save_competitions($items);
        $message = 'Shpallja u shtua.';
    }
    $items = oftk_get_competitions();
}

$pageTitle = 'Menaxho Konkurset';
$backLink = 'index.php';
require __DIR__ . '/_admin_layout.php';

if (empty($items)) {
    echo '<p class="card-meta">Nuk ka asnjë shpallje.</p>';
}
foreach ($items as $item):
?>
  <div class="admin-item">
    <div>
      <strong><?= htmlspecialchars($item['title'] ?? '') ?></strong>
      <span class="card-meta"><?= htmlspecialchars($item['date'] ?? '') ?></span>
    </div>
    <form method="post" style="display:inline;" onsubmit="return confirm('Fshi këtë shpallje?');">
      <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
      <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.75rem;">Fshi</button>
    </form>
  </div>
<?php endforeach; ?>

<h3 class="mt-2">Shto shpallje konkursi</h3>
<form method="post" class="admin-form">
  <div class="form-group">
    <label>Titulli</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Data / afati (p.sh. 15 Mars 2025)</label>
    <input type="text" name="date" class="form-control">
  </div>
  <div class="form-group">
    <label>Përmbledhje</label>
    <textarea name="excerpt" class="form-control" rows="2"></textarea>
  </div>
  <div class="form-group">
    <label>Lidhje</label>
    <input type="text" name="link" class="form-control" value="kontakt.html">
  </div>
  <button type="submit" class="btn btn-primary">Shto shpallje</button>
</form>
<?php require __DIR__ . '/_admin_footer.php'; ?>
