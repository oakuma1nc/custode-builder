(function () {
  var cfg = window.CUSTODE_EDITOR;
  if (!cfg || !window.grapesjs) return;

  function csrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function setStatus(msg, isErr) {
    var el = document.getElementById('editor-bar-status');
    if (!el) return;
    el.textContent = msg || '';
    el.classList.toggle('text-red-300', !!isErr);
    el.classList.toggle('text-amber-200/90', !isErr);
  }

  function hideLoading() {
    var lo = document.getElementById('editor-loading');
    if (lo) lo.style.display = 'none';
  }

  var editor = grapesjs.init({
    container: '#gjs',
    height: '100%',
    fromElement: false,
    storageManager: false,
  });

  var saveTimer;
  function scheduleSave() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveNow, 1800);
  }

  function saveNow() {
    var project;
    try {
      project = editor.getProjectData();
    } catch (e) {
      project = {};
    }
    var body = {
      csrf_token: csrf(),
      site_id: cfg.siteId,
      preview_token: cfg.previewToken,
      html: editor.getHtml(),
      css: editor.getCss(),
      gjs_components: JSON.stringify(project),
      gjs_styles: '',
    };
    fetch('/api/editor/save', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(body),
    })
      .then(function (r) {
        return r.json().then(function (j) {
          return { ok: r.ok, j: j };
        });
      })
      .then(function (x) {
        if (x.ok && x.j && x.j.ok) {
          setStatus('Saved', false);
        } else {
          setStatus('Save failed: ' + ((x.j && x.j.error) || 'error'), true);
        }
      })
      .catch(function () {
        setStatus('Save failed: network', true);
      });
  }

  editor.on('update', scheduleSave);

  var url = '/api/editor/load/' + encodeURIComponent(String(cfg.siteId)) + '?t=' + encodeURIComponent(cfg.previewToken);
  fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
    .then(function (r) {
      return r.json();
    })
    .then(function (data) {
      hideLoading();
      if (data.error) {
        setStatus('Load failed: ' + data.error, true);
        return;
      }
      var loaded = false;
      if (data.gjs_components && data.gjs_components.length > 2) {
        try {
          var pd = JSON.parse(data.gjs_components);
          editor.loadProjectData(pd);
          loaded = true;
        } catch (e) {
          loaded = false;
        }
      }
      if (!loaded) {
        var html = data.html && data.html.length ? data.html : '<div class="p-8"><h1>Your site</h1><p>Start editing.</p></div>';
        editor.setComponents(html, { allowScripts: true });
        if (data.css) {
          editor.setStyle(data.css);
        }
      }
      setStatus('Ready', false);
    })
    .catch(function () {
      hideLoading();
      setStatus('Could not load project', true);
    });
})();
