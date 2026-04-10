(function () {
  var form = document.getElementById('wizard-form');
  if (!form) return;

  var I = window.__CUSTODE_LANDING || {};
  function t(key, fallback) {
    return I[key] ? String(I[key]) : (fallback || key);
  }

  function formatStepLabel(tmpl, cur, tot) {
    var n = 0;
    return tmpl.replace(/%d/g, function () {
      n++;
      return n === 1 ? String(cur) : String(tot);
    });
  }

  document.querySelectorAll('.lang-switch').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
      e.preventDefault();
      window.location.href = a.getAttribute('href') + (window.location.hash || '');
    });
  });

  var totalSteps = 4;
  var step = 1;
  var stepLabelTmpl = form.getAttribute('data-step-label') || 'Step %d of %d';
  var stepOfEl = document.getElementById('wizard-step-of');
  var bars = document.querySelectorAll('#wizard-bars [data-bar]');
  var btnBack = document.getElementById('wizard-back');
  var btnNext = document.getElementById('wizard-next');
  var btnSubmit = document.getElementById('wizard-submit');

  function csrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function showStatus(on) {
    var el = document.getElementById('wizard-form-status');
    if (el) el.classList.toggle('is-visible', !!on);
  }

  function updateChrome() {
    if (stepOfEl) stepOfEl.textContent = formatStepLabel(stepLabelTmpl, step, totalSteps);
    bars.forEach(function (b) {
      var i = parseInt(b.getAttribute('data-bar'), 10);
      b.classList.toggle('on', i <= step);
    });
    document.querySelectorAll('.wizard-panel').forEach(function (p) {
      var s = parseInt(p.getAttribute('data-step'), 10);
      p.classList.toggle('active', s === step);
    });
    if (btnBack) btnBack.style.display = step > 1 ? 'inline-flex' : 'none';
    if (btnNext && btnSubmit) {
      if (step === totalSteps) {
        btnNext.style.display = 'none';
        btnSubmit.style.display = 'inline-flex';
      } else {
        btnNext.style.display = 'inline-flex';
        btnSubmit.style.display = 'none';
      }
    }
  }

  function val(id) {
    var el = document.getElementById(id);
    return el ? String(el.value || '').trim() : '';
  }

  function validateStep(s) {
    if (s === 1) {
      if (!val('w_business_name')) {
        alert(t('alert_business'));
        document.getElementById('w_business_name').focus();
        return false;
      }
      return true;
    }
    if (s === 2) {
      var d = val('w_description');
      if (d.length < 40) {
        alert(t('alert_prompt'));
        document.getElementById('w_description').focus();
        return false;
      }
      return true;
    }
    if (s === 3) {
      if (!val('w_name')) {
        alert(t('alert_name'));
        document.getElementById('w_name').focus();
        return false;
      }
      var em = val('w_email');
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) {
        alert(t('alert_email'));
        document.getElementById('w_email').focus();
        return false;
      }
      return true;
    }
    return true;
  }

  function fillReview() {
    var bt = document.getElementById('w_business_type');
    var typeLabel = bt && bt.options[bt.selectedIndex] ? bt.options[bt.selectedIndex].text : '';
    var elB = document.getElementById('w_rev_business_txt');
    var elT = document.getElementById('w_rev_type_txt');
    var elP = document.getElementById('w_rev_prompt_txt');
    var elC = document.getElementById('w_rev_contact_txt');
    if (elB) elB.textContent = val('w_business_name');
    if (elT) elT.textContent = typeLabel;
    if (elP) elP.textContent = val('w_description');
    if (elC) {
      var bits = [val('w_name'), val('w_email')];
      if (val('w_phone')) bits.push(val('w_phone'));
      elC.textContent = bits.join(' · ');
    }
  }

  if (btnNext) {
    btnNext.addEventListener('click', function () {
      if (!validateStep(step)) return;
      if (step === 3) fillReview();
      if (step < totalSteps) {
        step++;
        updateChrome();
      }
    });
  }

  if (btnBack) {
    btnBack.addEventListener('click', function () {
      if (step > 1) {
        step--;
        updateChrome();
      }
    });
  }

  updateChrome();

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!validateStep(1) || !validateStep(2) || !validateStep(3)) return;
    var prompt = val('w_description');
    if (prompt.length < 40) {
      alert(t('alert_prompt'));
      step = 2;
      updateChrome();
      return;
    }

    var payload = {
      csrf_token: csrf(),
      business_name: val('w_business_name'),
      name: val('w_name'),
      email: val('w_email'),
      phone: val('w_phone'),
      business_type: val('w_business_type') || 'restaurant',
      description: prompt,
      tagline: prompt.split('\n')[0].slice(0, 160),
      menu_or_services: '',
      highlights: '',
      address: val('w_address'),
      hours: val('w_hours'),
      cta: val('w_cta'),
      notes: 'Wizard submission.',
    };

    var btn = btnSubmit;
    if (btn) btn.disabled = true;
    showStatus(true);

    fetch('/api/brief', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(function (r) {
        return r.json().then(function (j) {
          return { ok: r.ok, status: r.status, j: j };
        });
      })
      .then(function (x) {
        var j = x.j || {};
        if (x.status === 419) {
          alert(t('alert_session'));
          if (btn) btn.disabled = false;
          showStatus(false);
          return;
        }
        if (x.status === 429) {
          alert(t('alert_ratelimit'));
          if (btn) btn.disabled = false;
          showStatus(false);
          return;
        }
        if (j.ok && j.preview_url) {
          window.location.href = j.preview_url;
          return;
        }
        if (j.status === 'failed' && j.preview_url) {
          window.location.href = j.preview_url;
          return;
        }
        var token = j.preview_token;
        if (token && (x.status === 202 || !j.ok)) {
          var tries = 0;
          var interval = setInterval(function () {
            tries++;
            if (tries > 100) {
              clearInterval(interval);
              if (j.preview_url) window.location.href = j.preview_url;
              if (btn) btn.disabled = false;
              showStatus(false);
              return;
            }
            fetch('/api/preview/status/' + encodeURIComponent(token), { headers: { Accept: 'application/json' } })
              .then(function (r) {
                return r.json();
              })
              .then(function (d) {
                if (d.ready && j.preview_url) {
                  clearInterval(interval);
                  window.location.href = j.preview_url;
                } else if (d.failed && j.preview_url) {
                  clearInterval(interval);
                  window.location.href = j.preview_url;
                }
              })
              .catch(function () {});
          }, 2000);
          return;
        }
        alert(j.error ? String(j.error) : t('alert_generic'));
        if (btn) btn.disabled = false;
        showStatus(false);
      })
      .catch(function () {
        alert(t('alert_network'));
        if (btn) btn.disabled = false;
        showStatus(false);
      });
  });
})();
