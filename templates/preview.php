<?php
/** @var array<string, mixed> $site */
/** @var string $token */
/** @var array<string, string> $pv */
/** @var string $locale */
$siteId = (int) ($site['id'] ?? 0);
$frameSrc = '/api/preview/frame/' . rawurlencode($token);
\Custode\Helpers\Auth::startSession();
$pvCsrf = \Custode\Helpers\Csrf::token();
$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$lang = (string) ($locale ?? 'it');
$pv = is_array($pv ?? null) ? $pv : [];
?>
<!DOCTYPE html>
<html lang="<?= $h($lang) ?>" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= $h($pvCsrf) ?>">
  <title><?= $h((string) ($pv['html_title'] ?? ($title ?? 'Preview'))) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/preview-paywall.css">
  <style>
    .preview-shell-inner { position: relative; width: 100%; height: 100%; background: #faf8f5; }
  </style>
</head>
<body>
  <header id="paywall-bar">
    <div class="paywall-brand">
      <img src="/public/img/custode-logo.svg" alt="" width="36" height="36">
      <div class="min-w-0">
        <p class="domain">custode.digital</p>
        <p class="title"><?= $h((string) ($pv['title'] ?? '')) ?></p>
        <p class="sub"><?= $h((string) ($pv['subtitle'] ?? '')) ?></p>
      </div>
    </div>
    <button type="button" id="unlock-btn" data-site-id="<?= $siteId ?>"><?= $h((string) ($pv['cta'] ?? 'Checkout')) ?></button>
  </header>
  <div id="preview-shell" class="preview-shell">
    <div class="preview-shell-inner">
      <iframe
        id="preview-frame"
        title="<?= $h((string) ($pv['frame_title'] ?? 'Preview')) ?>"
        class="absolute inset-0 w-full h-full border-0"
        style="position:absolute;inset:0;width:100%;height:100%;border:0;"
        sandbox="allow-scripts"
        src="<?= $h($frameSrc) ?>"
      ></iframe>
      <div id="preview-veil" class="preview-veil" style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,10,10,0.04) 0%,transparent 40%,rgba(10,10,10,0.06) 100%);" aria-hidden="true"></div>
    </div>
  </div>
  <script>
    (function () {
      var MSG = <?= json_encode([
          'checkout' => (string) ($pv['checkout_error'] ?? 'Checkout could not be started.'),
          'network' => (string) ($pv['network_error'] ?? 'Network error.'),
      ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

      function syncPaywallOffset() {
        var bar = document.getElementById('paywall-bar');
        var shell = document.getElementById('preview-shell');
        if (bar && shell) shell.style.top = bar.offsetHeight + 'px';
      }
      syncPaywallOffset();
      window.addEventListener('resize', syncPaywallOffset);

      var veil = document.getElementById('preview-veil');
      var shell = document.getElementById('preview-shell');
      if (veil) veil.addEventListener('contextmenu', function (e) { e.preventDefault(); });
      if (shell) shell.addEventListener('contextmenu', function (e) { e.preventDefault(); }, true);

      var btn = document.getElementById('unlock-btn');
      if (!btn) return;
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-site-id');
        btn.disabled = true;
        var m = document.querySelector('meta[name="csrf-token"]');
        var c = m ? m.getAttribute('content') : '';
        fetch('/api/checkout/' + encodeURIComponent(id), {
          method: 'POST',
          headers: { Accept: 'application/json', 'X-CSRF-Token': c },
          credentials: 'same-origin'
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
          .then(function (x) {
            if (x.ok && x.j && x.j.url) {
              window.location.href = x.j.url;
              return;
            }
            alert((x.j && x.j.error) ? x.j.error : MSG.checkout);
            btn.disabled = false;
          })
          .catch(function () {
            alert(MSG.network);
            btn.disabled = false;
          });
      });
    })();
  </script>
</body>
</html>
