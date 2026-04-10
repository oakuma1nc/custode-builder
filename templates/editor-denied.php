<?php
/** @var string $title */
/** @var int $site_id */
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
      <div class="cas-card cas-card--wide">
        <h1 class="cas-title cas-title--center" style="color:var(--accent);margin-bottom:8px;">Editor access required</h1>
        <p class="cas-subtitle cas-subtitle--mb" style="text-align:center;">Open the editor from your payment confirmation page, or append your preview token:</p>
        <code class="cas-code-inline">/editor/<?= (int) $site_id ?>?t=YOUR_PREVIEW_TOKEN</code>
        <div class="cas-inline-actions">
          <a href="/admin" class="cas-link">Admin dashboard</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
