<?php
/** @var string $title */
/** @var string|null $error */
\Custode\Helpers\Auth::startSession();
$loginCsrf = \Custode\Helpers\Csrf::token();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/custode-app-shell.css">
</head>
<body class="cas-body">
  <div class="cas-page-center">
    <div class="cas-card">
      <div class="cas-brand-row">
        <img src="/public/img/custode-logo.svg" alt="" width="40" height="40">
        <div>
          <p class="cas-eyebrow">custode.digital</p>
          <h1 class="cas-title">Custode Builder</h1>
        </div>
      </div>
      <?php if (!empty($error)): ?>
        <p class="cas-error-box"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
      <form method="post" action="/admin" class="cas-stack">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($loginCsrf, ENT_QUOTES, 'UTF-8') ?>">
        <div>
          <label class="cas-label" for="username">Username</label>
          <input class="cas-input" type="text" id="username" name="username" required autocomplete="username">
        </div>
        <div>
          <label class="cas-label" for="password">Password</label>
          <input class="cas-input" type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="cas-btn cas-btn--primary cas-btn--block">Sign in</button>
      </form>
      <p class="cas-hint">Set <code>ADMIN_USER</code> and <code>ADMIN_PASSWORD_HASH</code> in your environment (use <code>password_hash()</code>).</p>
    </div>
  </div>
</body>
</html>
