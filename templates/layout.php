<?php
/** @var string $content */
/** @var string $title */
\Custode\Helpers\Auth::startSession();
$csrfMeta = \Custode\Helpers\Csrf::token();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= htmlspecialchars($csrfMeta, ENT_QUOTES, 'UTF-8') ?>">
  <title><?= htmlspecialchars($title ?? 'Custode Builder', ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/custode-app-shell.css">
</head>
<body class="cas-body">
  <?= $content ?>
</body>
</html>
