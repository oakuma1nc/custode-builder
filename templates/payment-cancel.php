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
      <h1 class="cas-title cas-title--lg cas-title--center">Checkout cancelled</h1>
      <p class="cas-subtitle cas-subtitle--mb" style="max-width:28rem;margin-left:auto;margin-right:auto;">No charge was made. You can return to your preview and try again whenever you are ready.</p>
      <a href="/start" class="cas-btn cas-btn--primary">Back to wizard</a>
    </div>
  </div>
</body>
</html>
