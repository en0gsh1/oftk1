<?php
/**
 * OFTK – Admin: menaxho fizioterapeutët (shto / fshi)
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';

oftk_require_admin();

$items = oftk_get_physiotherapists();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (string) $_POST['delete_id'];
        $items = array_values(array_filter($items, fn($x) => ($x['id'] ?? '') !== $id));
        oftk_save_physiotherapists($items);
        $message = 'Fizioterapeuti u fshi.';
    } elseif (!empty($_POST['name'])) {
        $items[] = [
            'id' => oftk_next_id($items),
            'name' => trim((string) $_POST['name']),
            'license' => trim((string) ($_POST['license'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'specialty' => trim((string) ($_POST['specialty'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
        ];
        oftk_save_physiotherapists($items);
        $message = 'Fizioterapeuti u shtua.';
    }
    $items = oftk_get_physiotherapists();
}

$pageTitle = 'Fizioterapeutët';
$backLink = 'index.php';
require __DIR__ . '/_admin_layout.php';
?>
<p class="card-meta" style="margin-bottom: 1rem;">Regjistri shfaqet në faqen <a href="../regjistri.html">Kërko Fizioterapeut</a>. Shtoni ose fshini fizioterapeutë.</p>

<?php if (empty($items)): ?>
  <p class="card-meta">Nuk ka asnjë fizioterapeut të regjistruar.</p>
<?php else: ?>
  <div class="admin-item" style="font-weight: 600; background: var(--gray-100);">
    <div>Emri · Licenca · Qyteti · Specialiteti</div>
    <div style="min-width: 4rem;">Veprime</div>
  </div>
  <?php foreach ($items as $item): ?>
  <div class="admin-item">
    <div>
      <strong><?= htmlspecialchars($item['name'] ?? '') ?></strong>
      <?php if (!empty($item['license'])): ?> · <span><?= htmlspecialchars($item['license']) ?></span><?php endif; ?>
      <?php if (!empty($item['city'])): ?> · <span><?= htmlspecialchars($item['city']) ?></span><?php endif; ?>
      <?php if (!empty($item['specialty'])): ?> · <span class="card-meta"><?= htmlspecialchars($item['specialty']) ?></span><?php endif; ?>
    </div>
    <form method="post" style="display:inline;" onsubmit="return confirm('Fshi këtë fizioterapeut nga regjistri?');">
      <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
      <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.75rem;">Fshi</button>
    </form>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

<h3 class="mt-2">Shto fizioterapeut</h3>
<form method="post" class="admin-form">
  <div class="form-group">
    <label>Emri dhe mbiemri *</label>
    <input type="text" name="name" class="form-control" required placeholder="p.sh. Arben Krasniqi">
  </div>
  <div class="form-group">
    <label>Licenca (p.sh. FZK-001)</label>
    <input type="text" name="license" class="form-control" placeholder="FZK-XXX">
  </div>
  <div class="form-group">
    <label>Qyteti</label>
    <input type="text" name="city" class="form-control" placeholder="Prishtinë">
  </div>
  <div class="form-group">
    <label>Specialiteti</label>
    <input type="text" name="specialty" class="form-control" placeholder="Ortopedi, Sport">
  </div>
  <div class="form-group">
    <label>Telefoni</label>
    <input type="text" name="phone" class="form-control" placeholder="+383 XX XXX XXX">
  </div>
  <div class="form-group">
    <label>Email</label>
    <input type="email" name="email" class="form-control" placeholder="emri@example.com">
  </div>
  <button type="submit" class="btn btn-primary">Shto fizioterapeut</button>
</form>
<?php require __DIR__ . '/_admin_footer.php'; ?>
