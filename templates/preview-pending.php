<?php
/** @var string $title */
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
      <img src="/public/img/custode-logo.svg" alt="" width="48" height="48" class="cas-logo-center">
      <h1 class="cas-title cas-title--center" style="color:var(--accent);margin-bottom:8px;">Almost there</h1>
      <p class="cas-subtitle cas-subtitle--mb" style="max-width:26rem;margin-left:auto;margin-right:auto;">We are still generating your preview, or content is not ready yet. Refresh in a moment or contact Custode if this persists.</p>
      <button type="button" onclick="location.reload()" class="cas-btn cas-btn--primary">Refresh</button>
    </div>
  </div>
</body>
</html>
