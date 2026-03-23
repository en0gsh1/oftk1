<?php
/**
 * OFTK – Member login
 */

declare(strict_types=1);

define('OFTK_APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

oftk_session_start();

if (oftk_is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $csrf = (string) ($_POST['_csrf'] ?? '');

    if (!oftk_csrf_verify($csrf)) {
        $error = 'Sesioni skadoi. Ju lutem provoni përsëri.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ju lutem vendosni një email të vlefshëm.';
    } elseif ($password === '') {
        $error = 'Ju lutem vendosni fjalëkalimin.';
    } else {
        $email = strtolower($email);
        $ip = oftk_client_ip();

        if (oftk_failed_attempts_count($email) >= OFTK_MAX_LOGIN_ATTEMPTS) {
            $error = 'Shumë tentativa të pasuksesshme. Provoni përsëri pas ' . OFTK_LOCKOUT_MINUTES . ' minutash.';
            oftk_record_login_attempt($email, $ip, false);
        } else {
            $user = oftk_user_by_email($email);
            if ($user && password_verify($password, $user['password_hash'])) {
                oftk_record_login_attempt($email, $ip, true);
                oftk_login_user($user);
                $return = (string) ($_GET['return'] ?? 'dashboard.php');
                $return = preg_replace('#^https?://[^/]+#', '', $return);
                $return = ltrim($return, '/');
                if ($return === '' || strpos($return, '//') !== false || !preg_match('#^[a-zA-Z0-9_.-]+\.(php|html)(\?.*)?$#', $return)) {
                    $return = 'dashboard.php';
                }
                header('Location: ' . $return);
                exit;
            }
            oftk_record_login_attempt($email, $ip, false);
            $error = 'Email ose fjalëkalim i gabuar. Provoni përsëri.';
        }
    }
}

$csrfToken = oftk_csrf_token();
$noUsersYet = count(oftk_get_users()) === 0;
$pageTitle = 'Hyr në Llogari';
$layoutMode = 'login';
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Hyrje në llogari për anëtarët e Odës së Fizioterapeutëve të Kosovës.">
  <title><?= htmlspecialchars($pageTitle) ?> | OFTK – Oda e Fizioterapeutëve të Kosovës</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<?php require __DIR__ . '/includes/header.php'; ?>
  <main>
    <section class="section">
      <div class="container">
        <div class="login-wrap">
          <h1><?= htmlspecialchars($pageTitle) ?></h1>
          <p class="text-center" style="color: var(--gray-500); margin-bottom: 1.25rem;">Zona e anëtarëve – hyrje e sigurt.</p>
          <?php if ($noUsersYet): ?>
            <div class="alert alert-error">
              Nuk ka ende asnjë llogari. Së pari <strong><a href="create-admin.php">hapni create-admin.php</a></strong> (p.sh. http://localhost:8000/create-admin.php) për të krijuar llogarinë administrator.
            </div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
          <?php endif; ?>
          <form method="post" action="login.php" id="loginForm" autocomplete="on">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="form-group">
              <label for="loginEmail">Email</label>
              <input type="email" id="loginEmail" name="email" class="form-control" required
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                     placeholder="email@oftk-ks.org" autocomplete="email">
            </div>
            <div class="form-group">
              <label for="loginPassword">Fjalëkalimi</label>
              <input type="password" id="loginPassword" name="password" class="form-control" required
                     autocomplete="current-password" minlength="1">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">Hyr</button>
          </form>
          <p class="text-center mt-2" style="font-size: 0.9rem; color: var(--gray-500);">
            Këtu hyjnë vetëm anëtarët e regjistruar. Për pyetje rreth aksesit, na kontaktoni në
            <a href="mailto:info@oftk-ks.org">info@oftk-ks.org</a>.
          </p>
        </div>
      </div>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
  <script src="js/main.js"></script>
</body>
</html>
