<?php
/** @var string $title */
?>
<header class="cas-brief-header">
  <div class="cas-brief-header-inner">
    <a href="/" class="cas-header-brand" style="text-decoration:none;color:inherit;">
      <img src="/public/img/custode-logo.svg" alt="" width="32" height="32">
      <span class="cas-link" style="font-size:16px;">Custode</span>
    </a>
    <span class="cas-small">Website brief</span>
  </div>
</header>
<main class="cas-brief-main">
  <h1 class="cas-title" style="font-size:28px;margin-bottom:8px;">Tell us about your business</h1>
  <p class="cas-lead" style="margin-bottom:28px;">We use this brief to generate a tailored one-page site preview. Most fields are optional beyond the basics.</p>
  <div id="brief-alert" class="cas-alert cas-alert--error is-hidden" role="alert"></div>
  <form id="brief-form" class="cas-stack" style="gap:0;" novalidate>
    <fieldset class="cas-fieldset cas-grid-2">
      <legend class="cas-sr-only">Contact</legend>
      <div class="cas-col-span-2">
        <label class="cas-label" for="business_name">Business name *</label>
        <input class="cas-input" id="business_name" name="business_name" required>
      </div>
      <div>
        <label class="cas-label" for="business_type">Business type</label>
        <select class="cas-input" id="business_type" name="business_type">
          <?php
          $types = ['restaurant', 'cafe', 'bar', 'bakery', 'hotel', 'retail', 'service', 'other'];
          foreach ($types as $t) {
              echo '<option value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(ucfirst($t), ENT_QUOTES, 'UTF-8') . '</option>';
          }
          ?>
        </select>
      </div>
      <div>
        <label class="cas-label" for="name">Your name *</label>
        <input class="cas-input" id="name" name="name" required>
      </div>
      <div>
        <label class="cas-label" for="email">Email *</label>
        <input class="cas-input" type="email" id="email" name="email" required>
      </div>
      <div>
        <label class="cas-label" for="phone">Phone</label>
        <input class="cas-input" type="tel" id="phone" name="phone">
      </div>
    </fieldset>
    <hr class="cas-divider">
    <fieldset class="cas-fieldset">
      <legend class="cas-fieldset-legend">Story &amp; content</legend>
      <div class="cas-stack" style="gap:16px;">
        <div>
          <label class="cas-label" for="tagline">Tagline</label>
          <input class="cas-input" id="tagline" name="tagline" placeholder="e.g. Neapolitan pizza on the Limmat">
        </div>
        <div>
          <label class="cas-label" for="description">About the business</label>
          <textarea class="cas-input" id="description" name="description" placeholder="What makes you special? Who do you serve?"></textarea>
        </div>
        <div>
          <label class="cas-label" for="menu_or_services">Menu / services</label>
          <textarea class="cas-input" id="menu_or_services" name="menu_or_services" placeholder="Signature dishes, drinks, or packages"></textarea>
        </div>
        <div>
          <label class="cas-label" for="highlights">Highlights</label>
          <input class="cas-input" id="highlights" name="highlights" placeholder="Awards, press, dietary options…">
        </div>
        <div class="cas-grid-2">
          <div>
            <label class="cas-label" for="address">Address</label>
            <input class="cas-input" id="address" name="address">
          </div>
          <div>
            <label class="cas-label" for="hours">Hours</label>
            <input class="cas-input" id="hours" name="hours" placeholder="Tue–Sun 11:30–22:00">
          </div>
        </div>
        <div>
          <label class="cas-label" for="cta">Primary call-to-action</label>
          <input class="cas-input" id="cta" name="cta" placeholder="e.g. Book a table, Order online">
        </div>
        <div>
          <label class="cas-label" for="notes">Anything else?</label>
          <textarea class="cas-input" id="notes" name="notes" style="min-height:80px;"></textarea>
        </div>
      </div>
    </fieldset>
    <div class="cas-brief-actions">
      <button type="submit" id="brief-submit" class="cas-btn cas-btn--primary">Generate preview</button>
      <span id="brief-status" class="cas-status-line is-hidden">Working… this can take a minute.</span>
    </div>
  </form>
</main>
<script src="/public/js/brief-form.js"></script>
