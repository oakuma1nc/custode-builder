<?php
/** @var string $title */
/** @var string $locale */
/** @var array<string, mixed> $t */
/** @var string $setup_chf */
/** @var bool $has_monthly */
/** @var string $monthly_chf */
\Custode\Helpers\Auth::startSession();
$csrf = \Custode\Helpers\Csrf::token();
$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$steps = $t['steps'] ?? [];
$portfolio = $t['portfolio_items'] ?? [];
$faq = $t['faq'] ?? [];
$stdMonth = ($has_monthly && $monthly_chf !== '') ? $monthly_chf : '49';
?>
<!DOCTYPE html>
<html lang="<?= $h((string) ($t['html_lang'] ?? 'it')) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= $h($csrf) ?>">
  <meta name="description" content="<?= $h((string) ($t['meta_description'] ?? '')) ?>">
  <title><?= $h($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/landing-custode.css">
</head>
<body id="top">

<div class="lang-bar">
  <?php foreach (\Custode\Helpers\Locale::ALLOWED as $code): ?>
    <a href="<?= $h(\Custode\Helpers\Locale::langHref($code)) ?>" class="lang-switch<?= $locale === $code ? ' active' : '' ?>" data-lang="<?= $h($code) ?>"><?= strtoupper($code) ?></a>
  <?php endforeach; ?>
</div>

<nav>
  <div class="nav-inner">
    <a href="/" class="nav-logo">
      <img src="/public/img/custode-logo.svg" alt="" class="nav-logo-mark" width="32" height="32">
      CUSTODE<span>.</span>
    </a>
    <div class="nav-links" id="nav-links">
      <a href="#how"><?= $h((string) ($t['nav_how'] ?? '')) ?></a>
      <a href="#portfolio"><?= $h((string) ($t['nav_portfolio'] ?? '')) ?></a>
      <a href="#pricing"><?= $h((string) ($t['nav_pricing'] ?? '')) ?></a>
      <a href="#about"><?= $h((string) ($t['nav_about'] ?? '')) ?></a>
      <a href="#contact"><?= $h((string) ($t['nav_contact'] ?? '')) ?></a>
      <a href="/start"><?= $h((string) ($t['nav_create'] ?? '')) ?></a>
      <a href="#cband" class="nav-cta"><?= $h((string) ($t['nav_whatsapp'] ?? 'WhatsApp')) ?></a>
      <a href="/admin" class="admin-pill">Admin</a>
    </div>
    <button type="button" class="mobile-toggle" id="nav-toggle" aria-label="Menu">&#9776;</button>
  </div>
</nav>

<section class="hero">
  <div class="container">
    <div class="hero-grid">
      <div class="fade-up">
        <p class="eyebrow"><?= $h((string) ($t['hero_eyebrow'] ?? '')) ?></p>
        <h1><?= $h((string) ($t['hero_title_before'] ?? '')) ?><em><?= $h((string) ($t['hero_title_em'] ?? '')) ?></em></h1>
        <p class="hero-sub"><?= $h((string) ($t['hero_sub'] ?? '')) ?></p>
        <div class="hero-ctas">
          <a href="/start" class="btn btn-primary"><?= $h((string) ($t['hero_cta_primary'] ?? '')) ?></a>
          <a href="#portfolio" class="btn btn-secondary"><?= $h((string) ($t['hero_cta_secondary'] ?? '')) ?> &rarr;</a>
        </div>
        <p class="hero-note" style="margin-top:28px;font-size:13px;color:var(--text-muted);"><?= $h((string) ($t['hero_note'] ?? '')) ?></p>
      </div>
      <div class="fade-up" style="animation-delay: 0.15s;">
        <div class="ba-card">
          <div class="ba-badge"><?= $h((string) ($t['ba_badge'] ?? '')) ?></div>
          <div class="ba-split">
            <div class="ba-side ba-before">
              <div class="ba-label"><?= $h((string) ($t['ba_before'] ?? '')) ?></div>
              <div class="ba-browser">
                <div class="ba-browser-bar">
                  <div class="ba-dot"></div><div class="ba-dot"></div><div class="ba-dot"></div>
                  <span class="ba-url">google.com</span>
                </div>
                <div class="ba-content">
                  <div class="ba-icon">&#128269;</div>
                  <?= $h((string) ($t['ba_before_text'] ?? '')) ?>
                </div>
              </div>
            </div>
            <div class="ba-side ba-after">
              <div class="ba-label"><?= $h((string) ($t['ba_after'] ?? '')) ?></div>
              <div class="ba-browser">
                <div class="ba-browser-bar">
                  <div class="ba-dot"></div><div class="ba-dot"></div><div class="ba-dot"></div>
                  <span class="ba-url">google.com</span>
                </div>
                <div class="ba-content">
                  <div class="ba-icon">&#10003;</div>
                  <?= $h((string) ($t['ba_after_text'] ?? '')) ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="steps-section section" id="how">
  <div class="container">
    <div class="section-header scroll-reveal">
      <p class="eyebrow"><?= $h((string) ($t['steps_eyebrow'] ?? '')) ?></p>
      <h2 class="section-title"><?= $h((string) ($t['steps_title'] ?? '')) ?></h2>
      <p class="section-sub"><?= $h((string) ($t['steps_sub'] ?? '')) ?></p>
    </div>
    <div class="steps-grid">
      <?php $n = 1; foreach ($steps as $step): ?>
        <div class="step-card scroll-reveal">
          <div class="step-num"><?= $n++ ?></div>
          <h3><?= $h((string) ($step['title'] ?? '')) ?></h3>
          <p><?= $h((string) ($step['body'] ?? '')) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="portfolio">
  <div class="container">
    <div class="section-header scroll-reveal">
      <p class="eyebrow"><?= $h((string) ($t['portfolio_eyebrow'] ?? '')) ?></p>
      <h2 class="section-title"><?= $h((string) ($t['portfolio_title'] ?? '')) ?></h2>
      <p class="section-sub"><?= $h((string) ($t['portfolio_sub'] ?? '')) ?></p>
    </div>
    <div class="portfolio-grid">
      <?php foreach ($portfolio as $item): ?>
        <div class="port-card scroll-reveal">
          <div class="port-preview">
            <span class="port-preview-icon">&#127837;</span>
            <span class="port-tag"><?= $h((string) ($item['tag'] ?? '')) ?></span>
          </div>
          <div class="port-info">
            <div class="port-biz"><?= $h((string) ($item['biz'] ?? '')) ?></div>
            <div class="port-city"><?= $h((string) ($item['city'] ?? '')) ?></div>
            <a href="/start" class="port-link"><?= $h((string) ($t['portfolio_visit'] ?? '')) ?> &rarr;</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="pricing-section section" id="pricing">
  <div class="container">
    <div class="section-header scroll-reveal">
      <p class="eyebrow"><?= $h((string) ($t['pricing_eyebrow'] ?? '')) ?></p>
      <h2 class="section-title"><?= $h((string) ($t['pricing_title'] ?? '')) ?></h2>
      <p class="section-sub"><?= $h((string) ($t['pricing_sub'] ?? '')) ?></p>
    </div>

    <div class="scroll-reveal">
      <div class="pricing-phase-label"><span>&#9670;</span> <?= $h((string) ($t['pricing_phase_setup'] ?? '')) ?></div>
      <div class="pricing-row pricing-row-2">
        <div class="price-card">
          <div class="price-tag"><?= $h((string) ($t['pricing_nova_tag'] ?? '')) ?></div>
          <div class="price-name"><?= $h((string) ($t['pricing_nova_name'] ?? '')) ?></div>
          <div class="price-desc"><?= $h((string) ($t['pricing_nova_desc'] ?? '')) ?></div>
          <div class="price-amount"><span class="currency">CHF</span> <?= $h($setup_chf) ?></div>
          <div class="price-period"><?= $h((string) ($t['pricing_nova_period'] ?? '')) ?></div>
        </div>
        <div class="price-card">
          <div class="price-tag"><?= $h((string) ($t['pricing_restore_tag'] ?? '')) ?></div>
          <div class="price-name"><?= $h((string) ($t['pricing_restore_name'] ?? '')) ?></div>
          <div class="price-desc"><?= $h((string) ($t['pricing_restore_desc'] ?? '')) ?></div>
          <div class="price-amount" style="font-size:28px;font-weight:800;letter-spacing:-1px;"><?= $h((string) ($t['pricing_restore_amount_note'] ?? '')) ?></div>
          <div class="price-period"><?= $h((string) ($t['pricing_nova_period'] ?? '')) ?></div>
        </div>
      </div>
    </div>

    <div class="scroll-reveal">
      <div class="pricing-phase-label"><span>&#9670;</span> <?= $h((string) ($t['pricing_phase_monthly'] ?? '')) ?></div>
      <div class="pricing-row pricing-row-3">
        <div class="price-card">
          <div class="price-tag"><?= $h((string) ($t['pricing_base_tag'] ?? '')) ?></div>
          <div class="price-name"><?= $h((string) ($t['pricing_base_name'] ?? '')) ?></div>
          <div class="price-desc"><?= $h((string) ($t['pricing_base_desc'] ?? '')) ?></div>
          <div class="price-amount"><span class="currency">CHF</span> 19<span class="currency">/mo</span></div>
          <ul class="price-features">
            <li><?= $h((string) ($t['pricing_feature_domain'] ?? '')) ?></li>
            <li><?= $h((string) ($t['pricing_feature_hosting'] ?? '')) ?></li>
            <li><?= $h((string) ($t['pricing_feature_online'] ?? '')) ?></li>
          </ul>
        </div>
        <div class="price-card featured">
          <div class="price-badge"><?= $h((string) ($t['pricing_featured_badge'] ?? '')) ?></div>
          <div class="price-tag"><?= $h((string) ($t['pricing_standard_tag'] ?? '')) ?></div>
          <div class="price-name"><?= $h((string) ($t['pricing_standard_name'] ?? '')) ?></div>
          <div class="price-desc"><?= $h((string) ($t['pricing_standard_desc'] ?? '')) ?></div>
          <div class="price-amount"><span class="currency">CHF</span> <?= $h($stdMonth) ?><span class="currency">/mo</span></div>
          <ul class="price-features">
            <li><?= $h((string) ($t['pricing_feature_std_base'] ?? '')) ?></li>
            <li><?= $h((string) ($t['pricing_feature_updates'] ?? '')) ?></li>
            <li><?= $h((string) ($t['pricing_feature_wa'] ?? '')) ?></li>
            <li><?= $h((string) ($t['pricing_feature_google'] ?? '')) ?></li>
          </ul>
        </div>
        <div class="price-card">
          <div class="price-tag"><?= $h((string) ($t['pricing_premium_tag'] ?? '')) ?></div>
          <div class="price-name"><?= $h((string) ($t['pricing_premium_name'] ?? '')) ?></div>
          <div class="price-desc"><?= $h((string) ($t['pricing_premium_desc'] ?? '')) ?></div>
          <div class="price-amount"><span class="currency">CHF</span> 99<span class="currency">/mo</span></div>
          <ul class="price-features">
            <li><?= $h((string) ($t['pricing_feature_prem_std'] ?? '')) ?></li>
            <li><?= $h((string) ($t['pricing_feature_seo'] ?? '')) ?></li>
            <li><?= $h((string) ($t['pricing_feature_new'] ?? '')) ?></li>
            <li><?= $h((string) ($t['pricing_feature_priority'] ?? '')) ?></li>
          </ul>
        </div>
      </div>
    </div>
    <p class="pricing-note"><?= $h((string) ($t['pricing_monthly_disclaimer'] ?? '')) ?></p>
  </div>
</section>

<section class="cband" id="cband">
  <div class="container scroll-reveal">
    <h2><?= $h((string) ($t['cband_title'] ?? '')) ?></h2>
    <p><?= $h((string) ($t['cband_sub'] ?? '')) ?></p>
    <div class="cband-btns">
      <a href="#contact" class="cband-btn">&#128172; <?= $h((string) ($t['cband_wa'] ?? '')) ?></a>
      <a href="#contact" class="cband-btn">&#9992; <?= $h((string) ($t['cband_tg'] ?? '')) ?></a>
      <a href="#contact" class="cband-btn">&#9742; <?= $h((string) ($t['cband_call'] ?? '')) ?></a>
    </div>
  </div>
</section>

<section class="section" id="faq">
  <div class="container">
    <div class="section-header scroll-reveal">
      <p class="eyebrow"><?= $h((string) ($t['faq_eyebrow'] ?? '')) ?></p>
      <h2 class="section-title"><?= $h((string) ($t['faq_title'] ?? '')) ?></h2>
    </div>
    <div class="faq-list">
      <?php foreach ($faq as $item): ?>
        <div class="faq-item scroll-reveal">
          <button type="button" class="faq-q" data-faq-toggle>
            <?= $h((string) ($item['q'] ?? '')) ?>
            <span class="faq-icon">+</span>
          </button>
          <div class="faq-a">
            <p><?= $h((string) ($item['a'] ?? '')) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="about-section section" id="about">
  <div class="container">
    <div class="about-grid">
      <div class="scroll-reveal">
        <p class="eyebrow"><?= $h((string) ($t['about_eyebrow'] ?? '')) ?></p>
        <h2 class="section-title"><?= $h((string) ($t['about_title'] ?? '')) ?></h2>
        <div class="about-text">
          <p><?= $h((string) ($t['about_p1'] ?? '')) ?></p>
          <p><?= $h((string) ($t['about_p2'] ?? '')) ?></p>
          <p><?= $h((string) ($t['about_p3'] ?? '')) ?></p>
        </div>
        <div class="about-tagline"><?= $h((string) ($t['about_tagline'] ?? '')) ?></div>
        <p style="margin-top: 12px; font-size: 13px; color: var(--text-muted);"><?= $h((string) ($t['about_oakuma'] ?? '')) ?></p>
      </div>
      <div class="about-visual scroll-reveal">
        <div class="about-emblem">
          <div class="about-emblem-icon">&#9965;</div>
          <div class="about-emblem-text">Custode</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="contact">
  <div class="container">
    <div class="scroll-reveal" style="margin-bottom: 64px;">
      <p class="eyebrow"><?= $h((string) ($t['contact_eyebrow'] ?? '')) ?></p>
      <h2 class="section-title"><?= $h((string) ($t['contact_title'] ?? '')) ?></h2>
      <p class="section-sub"><?= $h((string) ($t['contact_sub'] ?? '')) ?></p>
    </div>
    <div class="contact-grid">
      <div class="contact-channels scroll-reveal">
        <a href="#cband" class="channel-card">
          <div class="channel-icon">&#128172;</div>
          <div>
            <div class="channel-name"><?= $h((string) ($t['ch_whatsapp'] ?? '')) ?></div>
            <div class="channel-sub"><?= $h((string) ($t['ch_whatsapp_sub'] ?? '')) ?></div>
          </div>
        </a>
        <a href="#cband" class="channel-card">
          <div class="channel-icon">&#9992;</div>
          <div>
            <div class="channel-name"><?= $h((string) ($t['ch_tg'] ?? '')) ?></div>
            <div class="channel-sub"><?= $h((string) ($t['ch_tg_sub'] ?? '')) ?></div>
          </div>
        </a>
        <a href="#cband" class="channel-card">
          <div class="channel-icon">&#9742;</div>
          <div>
            <div class="channel-name"><?= $h((string) ($t['ch_phone'] ?? '')) ?></div>
            <div class="channel-sub"><?= $h((string) ($t['ch_phone_sub'] ?? '')) ?></div>
          </div>
        </a>
      </div>
      <div id="create" class="scroll-reveal wizard-home-card">
        <p><?= $h((string) ($t['home_wizard_teaser'] ?? '')) ?></p>
        <a href="/start" class="btn btn-primary" style="width:100%;justify-content:center;"><?= $h((string) ($t['home_wizard_button'] ?? '')) ?></a>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="/" class="nav-logo" style="font-size:22px;display:inline-flex;align-items:center;gap:10px;">
          <img src="/public/img/custode-logo.svg" alt="" class="nav-logo-mark" width="36" height="36">
          CUSTODE<span style="color:var(--accent);">.</span>
        </a>
        <p class="footer-brand-tagline"><?= $h((string) ($t['footer_tagline'] ?? '')) ?></p>
      </div>
      <div>
        <div class="footer-col-title"><?= $h((string) ($t['footer_nav'] ?? '')) ?></div>
        <ul class="footer-links">
          <li><a href="#how"><?= $h((string) ($t['nav_how'] ?? '')) ?></a></li>
          <li><a href="#portfolio"><?= $h((string) ($t['nav_portfolio'] ?? '')) ?></a></li>
          <li><a href="#pricing"><?= $h((string) ($t['nav_pricing'] ?? '')) ?></a></li>
          <li><a href="#about"><?= $h((string) ($t['nav_about'] ?? '')) ?></a></li>
          <li><a href="#contact"><?= $h((string) ($t['nav_contact'] ?? '')) ?></a></li>
          <li><a href="/start"><?= $h((string) ($t['nav_create'] ?? '')) ?></a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title"><?= $h((string) ($t['footer_contact'] ?? '')) ?></div>
        <ul class="footer-links">
          <li><a href="#cband"><?= $h((string) ($t['nav_whatsapp'] ?? '')) ?></a></li>
          <li><a href="#cband">Telegram</a></li>
          <li><a href="mailto:<?= $h((string) ($t['footer_email'] ?? '')) ?>"><?= $h((string) ($t['footer_email'] ?? '')) ?></a></li>
          <li><a href="#contact"><?= $h((string) ($t['footer_place'] ?? '')) ?></a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title"><?= $h((string) ($t['footer_social'] ?? '')) ?></div>
        <ul class="footer-links">
          <li><a href="#">Instagram</a></li>
          <li><a href="#">TikTok</a></li>
          <li><a href="#">YouTube</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="footer-legal"><?= $h((string) ($t['footer_rights'] ?? '')) ?></div>
      <div class="footer-legal-links">
        <a href="#"><?= $h((string) ($t['footer_privacy'] ?? '')) ?></a>
        <a href="#"><?= $h((string) ($t['footer_terms'] ?? '')) ?></a>
        <a href="#"><?= $h((string) ($t['footer_cookies'] ?? '')) ?></a>
      </div>
    </div>
    <p style="text-align:center;margin-top:24px;font-size:12px;color:var(--text-muted);"><?= $h((string) ($t['footer_made'] ?? '')) ?></p>
  </div>
</footer>

<script src="/public/js/landing.js"></script>
</body>
</html>
