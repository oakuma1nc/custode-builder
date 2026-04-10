<?php
/** @var string $title */
/** @var list<array<string, mixed>> $sites */
/** @var bool $monthly_available */
$monthly_available = !empty($monthly_available);
\Custode\Helpers\Auth::startSession();
$dashCsrf = \Custode\Helpers\Csrf::token();
?>
<header class="cas-header">
  <div class="cas-header-inner">
    <div class="cas-header-brand">
      <img src="/public/img/custode-logo.svg" alt="" width="36" height="36">
      <div>
        <p class="cas-header-eyebrow">custode.digital</p>
        <h1 class="cas-header-title">Custode Builder</h1>
      </div>
    </div>
    <div class="cas-header-actions">
      <a href="/start" class="cas-link">New site</a>
      <form method="post" action="/api/auth/logout" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($dashCsrf, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="cas-link-muted">Log out</button>
      </form>
    </div>
  </div>
</header>
<main class="cas-main">
  <p class="cas-lead">All generated sites, previews, and deployment status.</p>
  <div class="cas-filter">
    <label for="dash-filter" class="cas-sr-only">Filter sites</label>
    <input type="search" id="dash-filter" class="cas-input" placeholder="Filter by business, client, email, status, ID…" autocomplete="off">
  </div>
  <div class="cas-table-shell">
    <table class="cas-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Business</th>
          <th>Client</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="dash-tbody">
        <?php foreach ($sites as $row): ?>
          <?php
            $id = (int) ($row['id'] ?? 0);
            $status = htmlspecialchars((string) ($row['status'] ?? ''), ENT_QUOTES, 'UTF-8');
            $biz = htmlspecialchars((string) ($row['business_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $client = htmlspecialchars((string) ($row['client_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars((string) ($row['client_email'] ?? ''), ENT_QUOTES, 'UTF-8');
            $token = rawurlencode((string) ($row['preview_token'] ?? ''));
            $previewUrl = '/preview/' . $token;
            $genFailed = (($row['status'] ?? '') === 'generating' && empty($row['html_content']));
            $filterHay = strtolower(
                (string) ($row['business_name'] ?? '')
                . ' ' . (string) ($row['client_name'] ?? '')
                . ' ' . (string) ($row['client_email'] ?? '')
                . ' ' . (string) ($row['status'] ?? '')
                . ' ' . (string) $id
            );
          ?>
          <tr class="dash-row" data-filter="<?= htmlspecialchars($filterHay, ENT_QUOTES, 'UTF-8') ?>">
            <td class="cas-mono"><?= $id ?></td>
            <td class="cas-td-strong"><?= $biz ?></td>
            <td>
              <div><?= $client ?></div>
              <div class="cas-small"><?= $email ?></div>
            </td>
            <td>
              <span class="cas-badge"><?= $status ?></span>
              <?php if ($genFailed): ?>
                <span class="cas-warn">Still generating or stuck — retry below.</span>
              <?php endif; ?>
              <?php if (($row['status'] ?? '') === 'failed' && !empty($row['generation_error'])): ?>
                <?php
                  $ge = (string) $row['generation_error'];
                  $snip = strlen($ge) > 140 ? substr($ge, 0, 137) . '…' : $ge;
                ?>
                <span class="cas-err-inline"><?= htmlspecialchars($snip, ENT_QUOTES, 'UTF-8') ?></span>
              <?php endif; ?>
            </td>
            <td>
              <div class="cas-actions-row">
                <a class="cas-action" href="<?= htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8') ?>">Preview</a>
                <?php if (in_array($row['status'] ?? '', ['paid', 'editing', 'deployed', 'live'], true)): ?>
                  <a class="cas-action cas-action--navy" href="/editor/<?= $id ?>?t=<?= htmlspecialchars((string) ($row['preview_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">Editor</a>
                <?php endif; ?>
                <button type="button" class="cas-action cas-action--navy js-regen" data-id="<?= $id ?>">Regenerate</button>
                <button type="button" class="cas-action cas-action--navy js-deploy" data-id="<?= $id ?>">Deploy</button>
                <?php if ($monthly_available && in_array($row['status'] ?? '', ['paid', 'editing', 'deployed', 'live'], true)): ?>
                  <button type="button" class="cas-action cas-action-outline js-monthly" data-id="<?= $id ?>" title="Requires STRIPE_MONTHLY_PRICE_ID">Hosting</button>
                <?php endif; ?>
              </div>
              <?php if (!empty($row['live_url'])): ?>
                <div class="cas-live-link"><a class="cas-external-link" href="<?= htmlspecialchars((string) $row['live_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Live URL</a></div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($sites === []): ?>
          <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">No sites yet. <a class="cas-link" href="/start">Open the wizard</a>.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <p id="dash-toast" class="cas-toast is-hidden" role="status"></p>
</main>
<script>
(function () {
  function toast(msg) {
    var el = document.getElementById('dash-toast');
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('is-hidden');
    setTimeout(function () { el.classList.add('is-hidden'); }, 10000);
  }
  function csrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }
  function postJson(url) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-Token': csrf()
      }
    }).then(function (r) {
      return r.json().then(function (j) { return { ok: r.ok, status: r.status, j: j }; }).catch(function () {
        return { ok: r.ok, status: r.status, j: {} };
      });
    });
  }
  var filterEl = document.getElementById('dash-filter');
  if (filterEl) {
    filterEl.addEventListener('input', function () {
      var q = (filterEl.value || '').toLowerCase().trim();
      document.querySelectorAll('tr.dash-row').forEach(function (tr) {
        var hay = (tr.getAttribute('data-filter') || '').toLowerCase();
        tr.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
      });
    });
  }
  document.querySelectorAll('.js-regen').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-id');
      btn.disabled = true;
      postJson('/api/generate/' + encodeURIComponent(id)).then(function (x) {
        var err = (x.j && (x.j.error || x.j.message)) ? String(x.j.error || x.j.message) : '';
        toast(x.ok && x.j && x.j.ok ? 'Regeneration finished.' : ('Regenerate failed' + (err ? ': ' + err : '.')));
        if (x.ok && x.j && x.j.ok) location.reload();
        btn.disabled = false;
      }).catch(function () { toast('Network error'); btn.disabled = false; });
    });
  });
  document.querySelectorAll('.js-deploy').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-id');
      btn.disabled = true;
      postJson('/api/deploy/' + encodeURIComponent(id)).then(function (x) {
        var err = (x.j && x.j.error) ? String(x.j.error) : '';
        toast(x.ok && x.j && x.j.ok ? ('Deployed: ' + (x.j.live_url || 'ok')) : ('Deploy failed' + (err ? ': ' + err : '.')));
        if (x.ok && x.j && x.j.ok) location.reload();
        btn.disabled = false;
      }).catch(function () { toast('Network error'); btn.disabled = false; });
    });
  });
  document.querySelectorAll('.js-monthly').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-id');
      btn.disabled = true;
      postJson('/api/checkout/monthly/' + encodeURIComponent(id)).then(function (x) {
        if (x.j && x.j.url) {
          window.location.href = x.j.url;
          return;
        }
        var err = (x.j && x.j.error) ? String(x.j.error) : 'unknown error';
        toast('Checkout: ' + err);
        btn.disabled = false;
      }).catch(function () { toast('Network error'); btn.disabled = false; });
    });
  });
})();
</script>
