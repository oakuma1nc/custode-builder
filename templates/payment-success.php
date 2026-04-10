<?php
/** @var string $title */
/** @var string|null $session_id */
/** @var string|null $error */
/** @var string|null $editor_url */
/** @var int $site_id */
$siteId = (int) ($site_id ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
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
    <div class="cas-page-inner">
      <div class="cas-card cas-card--wide cas-card--center cas-success-inner">
        <img src="/public/img/custode-logo.svg" alt="" width="48" height="48" class="cas-logo-center">
        <?php if (!empty($error)): ?>
          <h1 class="cas-title cas-title--center">Payment</h1>
          <p class="cas-error-box" style="text-align:left;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
          <h1 class="cas-title cas-title--lg cas-title--center">Thank you</h1>
          <p class="cas-subtitle cas-subtitle--mb">Your payment was received. You can open the visual editor while we publish your files to hosting.</p>
          <?php if ($siteId > 0): ?>
            <p id="deploy-line" class="cas-deploy-line" role="status">Checking deployment status…</p>
          <?php endif; ?>
          <?php if (!empty($editor_url)): ?>
            <a href="<?= htmlspecialchars($editor_url, ENT_QUOTES, 'UTF-8') ?>" class="cas-btn cas-btn--primary cas-btn--block" style="margin-bottom:16px;">Open visual editor</a>
          <?php endif; ?>
          <p class="cas-hint" style="margin-top:0;">Bookmark your editor link. Deployment can take up to a few minutes on first publish.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if (empty($error) && $siteId > 0): ?>
  <script>
  (function () {
    var sid = <?= $siteId ?>;
    var el = document.getElementById('deploy-line');
    var n = 0;
    var t = setInterval(function () {
      n++;
      if (n > 48) {
        if (el) el.textContent = 'Deployment is still in progress. Refresh the dashboard or try the live link later.';
        clearInterval(t);
        return;
      }
      fetch('/api/site/' + sid + '/deploy-status', { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (!el) return;
          if (d.error) {
            el.textContent = 'Could not read deploy status yet — your session may still be syncing.';
            return;
          }
          if (d.deployed && d.live_url) {
            el.textContent = '';
            var s = document.createElement('span');
            s.textContent = 'Live site: ';
            var a = document.createElement('a');
            a.href = d.live_url;
            a.className = 'cas-external-link';
            a.target = '_blank';
            a.rel = 'noopener';
            a.textContent = 'open';
            el.appendChild(s);
            el.appendChild(a);
            clearInterval(t);
          }
        })
        .catch(function () {});
    }, 2500);
  })();
  </script>
  <?php endif; ?>
</body>
</html>
