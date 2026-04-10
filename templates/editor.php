<?php
/** @var array<string, mixed> $site */
$sid = (int) ($site['id'] ?? 0);
$pt = (string) ($site['preview_token'] ?? '');
?>
<div class="cas-editor-shell">
  <div class="cas-editor-bar">
    <div class="cas-editor-bar-left">
      <img src="/public/img/custode-logo.svg" alt="" width="28" height="28">
      <span class="cas-editor-title">Visual editor</span>
      <span class="cas-editor-id">#<?= $sid ?></span>
    </div>
    <span id="editor-bar-status" class="cas-editor-status" role="status"></span>
    <a href="/admin" class="cas-link" style="flex-shrink:0;">Dashboard</a>
  </div>
  <div class="cas-editor-stage">
    <div id="editor-loading" class="cas-editor-loading">Loading editor…</div>
    <div id="gjs"></div>
  </div>
</div>
<script>
window.CUSTODE_EDITOR = {
  siteId: <?= $sid ?>,
  previewToken: <?= json_encode($pt) ?>
};
</script>
