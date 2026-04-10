(function () {
  document.querySelectorAll('.lang-switch').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
      e.preventDefault();
      var h = window.location.hash || '';
      window.location.href = a.getAttribute('href') + h;
    });
  });

  var navToggle = document.getElementById('nav-toggle');
  var navLinks = document.getElementById('nav-links');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', function () {
      navLinks.classList.toggle('is-open');
    });
    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navLinks.classList.remove('is-open');
      });
    });
  }

  document.querySelectorAll('.scroll-reveal').forEach(function (el) {
    /* ensure initial hidden state matches CSS */
  });
  if ('IntersectionObserver' in window) {
    var obs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) entry.target.classList.add('visible');
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
    );
    document.querySelectorAll('.scroll-reveal').forEach(function (el) {
      obs.observe(el);
    });
  } else {
    document.querySelectorAll('.scroll-reveal').forEach(function (el) {
      el.classList.add('visible');
    });
  }

  document.querySelectorAll('[data-faq-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      if (item) item.classList.toggle('open');
    });
  });
})();
