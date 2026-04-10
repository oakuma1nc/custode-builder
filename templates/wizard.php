<?php
/** @var string $title */
/** @var string $locale */
/** @var array<string, mixed> $t */
\Custode\Helpers\Auth::startSession();
$csrf = \Custode\Helpers\Csrf::token();
$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$bizTypes = $t['business_types'] ?? [];
$jsI18n = $t['js'] ?? [];
$stepsTotal = 4;
?>
<!DOCTYPE html>
<html lang="<?= $h((string) ($t['html_lang'] ?? 'it')) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= $h($csrf) ?>">
  <title><?= $h($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/landing-custode.css">
  <script>window.__CUSTODE_LANDING = <?= json_encode($jsI18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
</head>
<body id="top" class="wizard-page">

<div class="lang-bar">
  <?php foreach (\Custode\Helpers\Locale::ALLOWED as $code): ?>
    <a href="<?= $h(\Custode\Helpers\Locale::langHref($code)) ?>" class="lang-switch<?= $locale === $code ? ' active' : '' ?>"><?= strtoupper($code) ?></a>
  <?php endforeach; ?>
</div>

<div class="wizard-wrap">
  <div class="wizard-nav-top">
    <a href="/">&larr; <?= $h((string) ($t['wizard_back_home'] ?? 'Home')) ?></a>
  </div>

  <div class="wizard-card">
    <h1><?= $h((string) ($t['wizard_title'] ?? '')) ?></h1>
    <p class="wizard-lead"><?= $h((string) ($t['wizard_sub'] ?? '')) ?></p>

    <div class="wizard-bars" id="wizard-bars" aria-hidden="true">
      <?php for ($i = 1; $i <= $stepsTotal; $i++): ?>
        <span class="<?= $i === 1 ? 'on' : '' ?>" data-bar="<?= $i ?>"></span>
      <?php endfor; ?>
    </div>
    <p class="wizard-step-label" id="wizard-step-of"><?= $h(sprintf((string) ($t['wizard_step_of'] ?? 'Step %d of %d'), 1, $stepsTotal)) ?></p>

    <form id="wizard-form" action="#" method="post" autocomplete="on" data-step-label="<?= $h((string) ($t['wizard_step_of'] ?? 'Step %d of %d')) ?>">
      <div class="wizard-panel active" data-step="1" role="tabpanel">
        <h2 class="wizard-step-title"><?= $h((string) ($t['wizard_step1_title'] ?? '')) ?></h2>
        <p class="wizard-step-sub"><?= $h((string) ($t['wizard_step1_sub'] ?? '')) ?></p>
        <label for="w_business_name"><?= $h((string) ($t['form_business_name'] ?? '')) ?></label>
        <input type="text" id="w_business_name" name="business_name" class="form-input" required>
        <label for="w_business_type" style="margin-top:14px;"><?= $h((string) ($t['form_type'] ?? '')) ?></label>
        <select id="w_business_type" name="business_type" class="form-input" style="cursor:pointer;">
          <?php foreach (['restaurant', 'cafe', 'bar', 'bakery', 'hotel', 'retail', 'service', 'other'] as $typeKey): ?>
            <option value="<?= $h($typeKey) ?>"><?= $h((string) ($bizTypes[$typeKey] ?? $typeKey)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="wizard-panel" data-step="2" role="tabpanel">
        <h2 class="wizard-step-title"><?= $h((string) ($t['wizard_step2_title'] ?? '')) ?></h2>
        <p class="wizard-step-sub"><?= $h((string) ($t['wizard_step2_sub'] ?? '')) ?></p>
        <label for="w_description"><?= $h((string) ($t['form_prompt_label'] ?? '')) ?></label>
        <textarea id="w_description" name="description" class="form-input" rows="10" minlength="40" required placeholder="<?= $h((string) ($t['form_prompt_placeholder'] ?? '')) ?>"></textarea>
        <p class="form-hint"><?= $h((string) ($t['form_tip'] ?? '')) ?></p>
      </div>

      <div class="wizard-panel" data-step="3" role="tabpanel">
        <h2 class="wizard-step-title"><?= $h((string) ($t['wizard_step3_title'] ?? '')) ?></h2>
        <p class="wizard-step-sub"><?= $h((string) ($t['wizard_step3_sub'] ?? '')) ?></p>
        <label for="w_name"><?= $h((string) ($t['form_your_name'] ?? '')) ?></label>
        <input type="text" id="w_name" name="name" class="form-input" required>
        <label for="w_email" style="margin-top:14px;"><?= $h((string) ($t['form_email'] ?? '')) ?></label>
        <input type="email" id="w_email" name="email" class="form-input" required>
        <label for="w_phone" style="margin-top:14px;"><?= $h((string) ($t['form_phone'] ?? '')) ?></label>
        <input type="tel" id="w_phone" name="phone" class="form-input">
      </div>

      <div class="wizard-panel" data-step="4" role="tabpanel">
        <h2 class="wizard-step-title"><?= $h((string) ($t['wizard_step4_title'] ?? '')) ?></h2>
        <p class="wizard-step-sub"><?= $h((string) ($t['wizard_step4_sub'] ?? '')) ?></p>
        <div class="wizard-review-box" id="w_rev_business"><strong><?= $h((string) ($t['wizard_review_business'] ?? '')) ?></strong><span id="w_rev_business_txt"></span></div>
        <div class="wizard-review-box" id="w_rev_type"><strong><?= $h((string) ($t['wizard_review_type'] ?? '')) ?></strong><span id="w_rev_type_txt"></span></div>
        <div class="wizard-review-box" id="w_rev_prompt"><strong><?= $h((string) ($t['wizard_review_prompt'] ?? '')) ?></strong><span id="w_rev_prompt_txt"></span></div>
        <div class="wizard-review-box" id="w_rev_contact"><strong><?= $h((string) ($t['wizard_review_contact'] ?? '')) ?></strong><span id="w_rev_contact_txt"></span></div>
        <label for="w_address"><?= $h((string) ($t['form_address'] ?? '')) ?></label>
        <input type="text" id="w_address" name="address" class="form-input">
        <label for="w_hours" style="margin-top:14px;"><?= $h((string) ($t['form_hours'] ?? '')) ?></label>
        <input type="text" id="w_hours" name="hours" class="form-input">
        <label for="w_cta" style="margin-top:14px;"><?= $h((string) ($t['form_cta'] ?? '')) ?></label>
        <input type="text" id="w_cta" name="cta" class="form-input">
        <p id="wizard-form-status" class="landing-form-status" role="status">
          <span class="landing-spin" aria-hidden="true"></span>
          <span><?= $h((string) ($t['form_generating'] ?? '')) ?></span>
        </p>
      </div>

      <div class="wizard-actions">
        <button type="button" class="btn btn-secondary" id="wizard-back" style="display:none;"><?= $h((string) ($t['wizard_back'] ?? '')) ?></button>
        <button type="button" class="btn btn-primary" id="wizard-next"><?= $h((string) ($t['wizard_next'] ?? '')) ?></button>
        <button type="submit" class="btn btn-primary" id="wizard-submit" style="display:none;"><?= $h((string) ($t['form_submit'] ?? '')) ?></button>
      </div>
      <p class="form-note" style="margin-top:16px;"><?= $h((string) ($t['form_legal'] ?? '')) ?></p>
    </form>
  </div>
</div>

<script src="/public/js/wizard.js"></script>
</body>
</html>
