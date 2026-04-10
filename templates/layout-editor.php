<?php
/** @var string $content */
/** @var string $title */
\Custode\Helpers\Auth::startSession();
$edCsrf = \Custode\Helpers\Csrf::token();
?>
<!DOCTYPE html>
<html lang="en" class="cas-doc-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= htmlspecialchars($edCsrf, ENT_QUOTES, 'UTF-8') ?>">
  <title><?= htmlspecialchars($title ?? 'Editor', ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
  <link rel="stylesheet" href="/public/css/custode-app-shell.css">
  <link rel="stylesheet" href="/public/css/app.css">
</head>
<body class="cas-body" style="margin:0;padding:0;min-height:100%;">
  <?= $content ?>
  <script src="https://unpkg.com/grapesjs"></script>
  <script src="/public/js/editor-init.js"></script>
</body>
</html>
