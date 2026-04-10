(function () {
  var form = document.getElementById('brief-form');
  if (!form) return;
  var alertEl = document.getElementById('brief-alert');
  var statusEl = document.getElementById('brief-status');
  var submitBtn = document.getElementById('brief-submit');

  function csrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function showAlert(type, message) {
    if (!alertEl) return;
    alertEl.classList.remove('is-hidden', 'cas-alert--error', 'cas-alert--warn', 'cas-alert--ok');
    if (type === 'error') {
      alertEl.classList.add('cas-alert--error');
    } else if (type === 'warn') {
      alertEl.classList.add('cas-alert--warn');
    } else {
      alertEl.classList.add('cas-alert--ok');
    }
    alertEl.textContent = message;
    alertEl.classList.remove('is-hidden');
  }

  function validateField(el, msg) {
    if (!el) return true;
    if (el.hasAttribute('required') && !String(el.value || '').trim()) {
      el.setCustomValidity(msg || 'Required');
      el.reportValidity();
      return false;
    }
    if (el.type === 'email' && el.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) {
      el.setCustomValidity('Enter a valid email');
      el.reportValidity();
      return false;
    }
    el.setCustomValidity('');
    return true;
  }

  function pollPreview(token, onDone) {
    var tries = 0;
    var timer = setInterval(function () {
      tries++;
      if (tries > 90) {
        clearInterval(timer);
        onDone('timeout');
        return;
      }
      fetch('/api/preview/status/' + encodeURIComponent(token), { headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.ready) {
            clearInterval(timer);
            onDone('ready');
          } else if (d.failed) {
            clearInterval(timer);
            onDone('failed', d.error || '');
          }
        })
        .catch(function () {});
    }, 2000);
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var bn = document.getElementById('business_name');
    var nm = document.getElementById('name');
    var em = document.getElementById('email');
    if (!validateField(bn, 'Business name is required')) return;
    if (!validateField(nm, 'Your name is required')) return;
    if (!validateField(em, 'Valid email is required')) return;

    var payload = { csrf_token: csrf() };
    var fd = new FormData(form);
    fd.forEach(function (v, k) {
      payload[k] = typeof v === 'string' ? v.trim() : v;
    });
    if (submitBtn) submitBtn.disabled = true;
    if (statusEl) statusEl.classList.remove('is-hidden');

    fetch('/api/brief', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(function (r) {
        return r.json().then(function (j) {
          return { ok: r.ok, status: r.status, j: j };
        });
      })
      .then(function (x) {
        var j = x.j || {};
        var token = j.preview_token;
        if (x.status === 419) {
          showAlert('error', j.error || 'Security token expired. Refresh the page.');
          if (submitBtn) submitBtn.disabled = false;
          if (statusEl) statusEl.classList.add('is-hidden');
          return;
        }
        if (x.status === 429) {
          showAlert('error', j.error || 'Too many requests.');
          if (submitBtn) submitBtn.disabled = false;
          if (statusEl) statusEl.classList.add('is-hidden');
          return;
        }
        if (j.ok && j.preview_url) {
          showAlert('ok', 'Preview ready. Redirecting…');
          setTimeout(function () {
            window.location.href = j.preview_url;
          }, 500);
          return;
        }
        if (j.status === 'failed' && j.preview_url) {
          showAlert('error', j.message || 'Generation failed.');
          setTimeout(function () {
            window.location.href = j.preview_url;
          }, 1200);
          if (submitBtn) submitBtn.disabled = false;
          if (statusEl) statusEl.classList.add('is-hidden');
          return;
        }
        if (token && (x.status === 202 || !j.ok)) {
          showAlert('warn', j.message || 'Still generating…');
          pollPreview(token, function (reason, errMsg) {
            if (reason === 'ready' && j.preview_url) {
              window.location.href = j.preview_url;
            } else if (reason === 'failed') {
              showAlert('error', errMsg || 'Generation failed.');
              if (j.preview_url) {
                setTimeout(function () {
                  window.location.href = j.preview_url;
                }, 1500);
              }
              if (submitBtn) submitBtn.disabled = false;
              if (statusEl) statusEl.classList.add('is-hidden');
            } else {
              showAlert('warn', 'Taking longer than expected. You can open the preview link manually.');
              if (j.preview_url) {
                setTimeout(function () {
                  window.location.href = j.preview_url;
                }, 4000);
              }
              if (submitBtn) submitBtn.disabled = false;
              if (statusEl) statusEl.classList.add('is-hidden');
            }
          });
          return;
        }
        showAlert('error', (j && j.error) ? j.error : 'Something went wrong.');
        if (submitBtn) submitBtn.disabled = false;
        if (statusEl) statusEl.classList.add('is-hidden');
      })
      .catch(function () {
        showAlert('error', 'Network error. Please try again.');
        if (submitBtn) submitBtn.disabled = false;
        if (statusEl) statusEl.classList.add('is-hidden');
      });
  });
})();
