<?php
/**
 * OFTK – Admin: manage photo gallery
 * Formati i vjetër: listë e sheshtë foto.
 * Formati i ri: { "albums": [ { "id", "title", "title_en", "photos": [...] } ] }
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';

oftk_require_admin();

function oftk_gallery_is_albums_format(array $data): bool
{
    return isset($data['albums']) && is_array($data['albums']);
}

/** Slug për id të albumit (ASCII). */
function oftk_gallery_make_album_id(string $s): string
{
    $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($t !== false && $t !== '') {
        $s = $t;
    }
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim((string) $s, '-');
    if ($s === '') {
        $s = 'album-' . bin2hex(random_bytes(3));
    }

    return $s;
}

/** Siguron id unik mes albumeve ekzistuese. */
function oftk_gallery_unique_album_id(array $albums, string $base): string
{
    $used = [];
    foreach ($albums as $a) {
        if (is_array($a) && isset($a['id'])) {
            $used[(string) $a['id']] = true;
        }
    }
    $id = $base;
    $n = 2;
    while (isset($used[$id])) {
        $id = $base . '-' . $n;
        ++$n;
    }

    return $id;
}

/** Indeksi i albumit sipas fushës id, ose -1. */
function oftk_gallery_find_album_index(array $albums, string $albumId): int
{
    foreach ($albums as $i => $a) {
        if (!is_array($a)) {
            continue;
        }
        if ((string) ($a['id'] ?? '') === $albumId) {
            return (int) $i;
        }
    }

    return -1;
}

$raw = oftk_get_gallery();
$albumsMode = oftk_gallery_is_albums_format($raw);
$message = '';

if ($albumsMode && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $albums = isset($raw['albums']) && is_array($raw['albums']) ? array_values($raw['albums']) : [];

    if ($action === 'add_album') {
        $title = trim((string) ($_POST['album_title'] ?? ''));
        $titleEn = trim((string) ($_POST['album_title_en'] ?? ''));
        $customId = trim((string) ($_POST['album_id_custom'] ?? ''));
        if ($title === '') {
            $message = 'Titulli i folderit është i detyrueshëm.';
        } else {
            $baseId = $customId !== '' ? oftk_gallery_make_album_id($customId) : oftk_gallery_make_album_id($title);
            $id = oftk_gallery_unique_album_id($albums, $baseId);
            $albums[] = [
                'id' => $id,
                'title' => $title,
                'title_en' => $titleEn,
                'photos' => [],
            ];
            $raw['albums'] = $albums;
            if (oftk_save_gallery($raw)) {
                $message = 'Folderi u shtua. Tani mund të shtoni foto brenda tij.';
            } else {
                $message = 'Ruajtja dështoi. Kontrolloni lejet e skedarit.';
            }
        }
    } elseif ($action === 'delete_album') {
        $aid = trim((string) ($_POST['album_id'] ?? ''));
        $idx = oftk_gallery_find_album_index($albums, $aid);
        if ($idx < 0) {
            $message = 'Folderi nuk u gjet.';
        } else {
            array_splice($albums, $idx, 1);
            $raw['albums'] = $albums;
            if (oftk_save_gallery($raw)) {
                $message = 'Folderi dhe fotot e tij u fshinë.';
            } else {
                $message = 'Ruajtja dështoi.';
            }
        }
    } elseif ($action === 'add_photo') {
        $aid = trim((string) ($_POST['album_id'] ?? ''));
        $img = trim((string) ($_POST['image'] ?? ''));
        $ptitle = trim((string) ($_POST['photo_title'] ?? ''));
        $caption = trim((string) ($_POST['caption'] ?? ''));
        $idx = oftk_gallery_find_album_index($albums, $aid);
        if ($idx < 0) {
            $message = 'Zgjidhni një folder valid.';
        } elseif ($img === '' || $ptitle === '') {
            $message = 'URL e imazhit dhe titulli i fotos janë të detyrueshëm.';
        } else {
            if (!isset($albums[$idx]['photos']) || !is_array($albums[$idx]['photos'])) {
                $albums[$idx]['photos'] = [];
            }
            $albums[$idx]['photos'][] = [
                'id' => oftk_gallery_next_photo_id($raw),
                'image' => $img,
                'title' => $ptitle,
                'caption' => $caption,
            ];
            $raw['albums'] = $albums;
            if (oftk_save_gallery($raw)) {
                $message = 'Fotoja u shtua në folder.';
            } else {
                $message = 'Ruajtja dështoi.';
            }
        }
    } elseif ($action === 'delete_photo') {
        $aid = trim((string) ($_POST['album_id'] ?? ''));
        $pid = trim((string) ($_POST['photo_id'] ?? ''));
        $idx = oftk_gallery_find_album_index($albums, $aid);
        if ($idx < 0) {
            $message = 'Folderi nuk u gjet.';
        } else {
            $photos = isset($albums[$idx]['photos']) && is_array($albums[$idx]['photos'])
                ? $albums[$idx]['photos']
                : [];
            $newPhotos = array_values(array_filter(
                $photos,
                static fn($p) => is_array($p) && (string) ($p['id'] ?? '') !== $pid
            ));
            if (count($newPhotos) === count($photos)) {
                $message = 'Fotoja nuk u gjet.';
            } else {
                $albums[$idx]['photos'] = $newPhotos;
                $raw['albums'] = $albums;
                if (oftk_save_gallery($raw)) {
                    $message = 'Fotoja u fshi.';
                } else {
                    $message = 'Ruajtja dështoi.';
                }
            }
        }
    }

    $raw = oftk_get_gallery();
}

if (!$albumsMode && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = $raw;
    if (isset($_POST['delete_id'])) {
        $id = (string) $_POST['delete_id'];
        $items = array_values(array_filter($items, fn($x) => ($x['id'] ?? '') !== $id));
        oftk_save_gallery($items);
        $message = 'Fotoja u fshi.';
    } elseif (!empty($_POST['title'])) {
        $items[] = [
            'id' => oftk_next_id($items),
            'image' => trim((string) ($_POST['image'] ?? '')),
            'title' => trim((string) $_POST['title']),
            'caption' => trim((string) ($_POST['caption'] ?? '')),
        ];
        oftk_save_gallery($items);
        $message = 'Fotoja u shtua.';
    }
    $raw = oftk_get_gallery();
}

$pageTitle = 'Menaxho Foto Galeria';
$backLink = 'index.php';
$adminListHeading = $albumsMode ? 'Folderët (albumet) dhe fotot' : null;
require __DIR__ . '/_admin_layout.php';

if ($albumsMode) {
    $albums = isset($raw['albums']) && is_array($raw['albums']) ? $raw['albums'] : [];
    ?>
    <p class="card-meta" style="margin-bottom:1.25rem;">
      Krijoni një <strong>folder</strong> për aktivitetin, pastaj shtoni foto me URL. Në faqen publike shfaqen sipas folderëve.
    </p>

    <h4 class="mt-2" style="font-size:1rem;margin-top:1.5rem;">Shto folder të ri</h4>
    <form method="post" class="admin-form" style="margin-bottom:2rem;">
      <input type="hidden" name="action" value="add_album">
      <div class="form-group">
        <label>Titulli (shqip)</label>
        <input type="text" name="album_title" class="form-control" required placeholder="p.sh. Trajnime 2025">
      </div>
      <div class="form-group">
        <label>Titulli (anglisht) — opsional</label>
        <input type="text" name="album_title_en" class="form-control" placeholder="e.g. Training 2025">
      </div>
      <div class="form-group">
        <label>ID e folderit (opsional)</label>
        <input type="text" name="album_id_custom" class="form-control" placeholder="Lëreni bosh për gjenerim automatik">
        <small class="card-meta">Vetëm shkronja/numra, për URL; nëse ekziston, shtohet sufiks.</small>
      </div>
      <button type="submit" class="btn btn-primary">Shto folder</button>
    </form>

    <h4 class="mt-2" style="font-size:1rem;">Shto foto në një folder</h4>
    <form method="post" class="admin-form" style="margin-bottom:2rem;">
      <input type="hidden" name="action" value="add_photo">
      <div class="form-group">
        <label>Folderi</label>
        <select name="album_id" class="form-control" required>
          <option value="">— Zgjidh folderin —</option>
          <?php foreach ($albums as $al): ?>
            <?php if (!is_array($al)) {
                continue;
            } ?>
            <option value="<?= htmlspecialchars((string) ($al['id'] ?? '')) ?>">
              <?= htmlspecialchars((string) ($al['title'] ?? $al['id'] ?? '')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>URL e imazhit</label>
        <input type="url" name="image" class="form-control" placeholder="https://..." required>
      </div>
      <div class="form-group">
        <label>Titulli i fotos</label>
        <input type="text" name="photo_title" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Caption / nën titull</label>
        <input type="text" name="caption" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary">Shto foto</button>
    </form>

    <?php foreach ($albums as $al): ?>
      <?php if (!is_array($al)) {
          continue;
      } ?>
      <?php
        $aid = (string) ($al['id'] ?? '');
        $t = (string) ($al['title'] ?? $aid);
        $photos = isset($al['photos']) && is_array($al['photos']) ? $al['photos'] : [];
      ?>
      <div class="admin-item" style="flex-direction:column;align-items:stretch;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
          <strong><?= htmlspecialchars($t) ?></strong>
          <form method="post" style="display:inline;" onsubmit="return confirm('Fshi folderin dhe të gjitha fotot brenda tij?');">
            <input type="hidden" name="action" value="delete_album">
            <input type="hidden" name="album_id" value="<?= htmlspecialchars($aid) ?>">
            <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.75rem;">Fshi folderin</button>
          </form>
        </div>
        <?php if ($photos === []): ?>
          <p class="card-meta" style="margin:0.5rem 0 0;">Nuk ka foto në këtë folder.</p>
        <?php else: ?>
          <?php foreach ($photos as $p): ?>
            <?php if (!is_array($p)) {
                continue;
            } ?>
            <div class="admin-item" style="margin-top:0.5rem;margin-bottom:0;">
              <div>
                <?php if (!empty($p['image'])): ?>
                  <img src="<?= htmlspecialchars((string) $p['image']) ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;vertical-align:middle;margin-right:8px;">
                <?php endif; ?>
                <strong><?= htmlspecialchars((string) ($p['title'] ?? '')) ?></strong>
                <span class="card-meta"><?= htmlspecialchars((string) ($p['caption'] ?? '')) ?></span>
              </div>
              <form method="post" style="display:inline;" onsubmit="return confirm('Fshi këtë foto?');">
                <input type="hidden" name="action" value="delete_photo">
                <input type="hidden" name="album_id" value="<?= htmlspecialchars($aid) ?>">
                <input type="hidden" name="photo_id" value="<?= htmlspecialchars((string) ($p['id'] ?? '')) ?>">
                <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.75rem;">Fshi foto</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <?php if ($albums === []): ?>
      <p class="card-meta">Nuk ka ende folderë. Shtoni një folder më sipër.</p>
    <?php endif; ?>

    <?php require __DIR__ . '/_admin_footer.php';

    return;
}

$items = $raw;

if (empty($items)) {
    echo '<p class="card-meta">Nuk ka asnjë foto.</p>';
}
foreach ($items as $item):
?>
  <div class="admin-item">
    <div>
      <?php if (!empty($item['image'])): ?><img src="<?= htmlspecialchars($item['image']) ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;vertical-align:middle;margin-right:8px;"><?php endif; ?>
      <strong><?= htmlspecialchars($item['title'] ?? '') ?></strong>
      <span class="card-meta"><?= htmlspecialchars($item['caption'] ?? '') ?></span>
    </div>
    <form method="post" style="display:inline;" onsubmit="return confirm('Fshi këtë foto?');">
      <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
      <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.75rem;">Fshi</button>
    </form>
  </div>
<?php endforeach; ?>

<h3 class="mt-2">Shto foto</h3>
<form method="post" class="admin-form">
  <div class="form-group">
    <label>URL e imazhit</label>
    <input type="url" name="image" class="form-control" placeholder="https://...">
  </div>
  <div class="form-group">
    <label>Titulli</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Caption / nën titull</label>
    <input type="text" name="caption" class="form-control">
  </div>
  <button type="submit" class="btn btn-primary">Shto foto</button>
</form>
<?php require __DIR__ . '/_admin_footer.php'; ?>
