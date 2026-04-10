<?php
/** @var string $title */
/** @var array<string, mixed> $site */
/** @var string $token */
$err = (string) ($site['generation_error'] ?? 'The AI could not produce a preview.');
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
        <img src="/public/img/custode-logo.svg" alt="" width="48" height="48" class="cas-logo-center">
        <h1 class="cas-title cas-title--center" style="color:var(--accent);margin-bottom:8px;">We hit a snag</h1>
        <p class="cas-subtitle cas-subtitle--mb" style="text-align:center;">Your brief was saved, but automatic generation did not finish. Custode support or your admin can retry from the dashboard.</p>
        <div class="cas-code-block"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="cas-inline-actions">
          <a href="/start" class="cas-btn cas-btn--primary">Try again</a>
          <a href="/admin" class="cas-link">Admin</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
