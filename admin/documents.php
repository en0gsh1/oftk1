<?php
/**
 * OFTK – Admin: manage documents
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';

oftk_require_admin();

$items = oftk_get_documents();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (string) $_POST['delete_id'];
        $items = array_values(array_filter($items, fn($x) => ($x['id'] ?? '') !== $id));
        oftk_save_documents($items);
        $message = 'Dokumenti u fshi.';
    } elseif (!empty($_POST['title'])) {
        $items[] = [
            'id' => oftk_next_id($items),
            'title' => trim((string) $_POST['title']),
            'meta' => trim((string) ($_POST['meta'] ?? 'PDF')),
            'url' => trim((string) ($_POST['url'] ?? '#')),
        ];
        oftk_save_documents($items);
        $message = 'Dokumenti u shtua.';
    }
    $items = oftk_get_documents();
}

$pageTitle = 'Menaxho Dokumentet';
$backLink = 'index.php';
require __DIR__ . '/_admin_layout.php';

if (empty($items)) {
    echo '<p class="card-meta">Nuk ka asnjë dokument.</p>';
}
foreach ($items as $item):
?>
  <div class="admin-item">
    <div>
      <strong><?= htmlspecialchars($item['title'] ?? '') ?></strong>
      <span class="card-meta"><?= htmlspecialchars($item['meta'] ?? '') ?></span>
    </div>
    <form method="post" style="display:inline;" onsubmit="return confirm('Fshi këtë dokument?');">
      <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
      <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.75rem;">Fshi</button>
    </form>
  </div>
<?php endforeach; ?>

<h3 class="mt-2">Shto dokument</h3>
<form method="post" class="admin-form">
  <div class="form-group">
    <label>Titulli</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Meta (p.sh. PDF · Janar 2025)</label>
    <input type="text" name="meta" class="form-control" value="PDF">
  </div>
  <div class="form-group">
    <label>Lidhja e shkarkimit (url)</label>
    <input type="text" name="url" class="form-control" placeholder="documents/emri.pdf">
  </div>
  <button type="submit" class="btn btn-primary">Shto dokument</button>
</form>
<?php require __DIR__ . '/_admin_footer.php'; ?>
