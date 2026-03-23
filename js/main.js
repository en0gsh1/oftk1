/**
 * OFTK – Main JS: sticky header, mobile menu, active nav link
 */
(function () {
  'use strict';

  const menuToggle = document.getElementById('menuToggle');
  const menuClose = document.getElementById('menuClose');
  const navMobile = document.getElementById('navMobile');

  /** Në telefon: zgjedhësi i gjuhës brenda menysë mobile (jo në shiritin e sipërm) */
  function relocateHeaderActionsForViewport() {
    var headerInner = document.querySelector('.site-header > .header-inner');
    var actions = headerInner ? headerInner.querySelector('.header-actions') : null;
    var navInner = document.querySelector('.nav-mobile-inner');
    var toggle = headerInner ? headerInner.querySelector('.menu-toggle') : null;
    if (!headerInner || !actions || !navInner || !toggle) return;

    var mq = window.matchMedia('(max-width: 991px)');
    if (mq.matches) {
      if (actions.parentNode !== navInner) navInner.appendChild(actions);
    } else {
      if (actions.parentNode !== headerInner) headerInner.insertBefore(actions, toggle);
    }
  }

  relocateHeaderActionsForViewport();
  try {
    window.matchMedia('(max-width: 991px)').addEventListener('change', relocateHeaderActionsForViewport);
  } catch (e) {
    window.matchMedia('(max-width: 991px)').addListener(relocateHeaderActionsForViewport);
  }
  window.__oftk_relocateHeader = relocateHeaderActionsForViewport;

  function openMobileMenu() {
    if (navMobile) {
      navMobile.classList.add('is-open');
      navMobile.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
    if (menuToggle) menuToggle.setAttribute('aria-expanded', 'true');
  }

  function collapseMobileAccordions() {
    if (!navMobile) return;
    navMobile.querySelectorAll('.nav-mobile-accordion-trigger').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
      var pid = btn.getAttribute('aria-controls');
      var panel = pid ? document.getElementById(pid) : null;
      if (panel) panel.hidden = true;
    });
  }

  function closeMobileMenu() {
    if (navMobile) {
      navMobile.classList.remove('is-open');
      navMobile.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      collapseMobileAccordions();
    }
    if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
  }

  if (menuToggle && navMobile) {
    menuToggle.addEventListener('click', openMobileMenu);
  }
  if (menuClose && navMobile) {
    menuClose.addEventListener('click', closeMobileMenu);
  }
  if (navMobile) {
    navMobile.addEventListener('click', function (e) {
      if (e.target === navMobile) closeMobileMenu();
    });
    navMobile.addEventListener('click', function (e) {
      var trigger = e.target.closest('.nav-mobile-accordion-trigger');
      if (!trigger || !navMobile.contains(trigger)) return;
      e.preventDefault();
      var expanded = trigger.getAttribute('aria-expanded') === 'true';
      var panelId = trigger.getAttribute('aria-controls');
      var panel = panelId ? document.getElementById(panelId) : null;
      navMobile.querySelectorAll('.nav-mobile-accordion-trigger').forEach(function (other) {
        if (other === trigger) return;
        other.setAttribute('aria-expanded', 'false');
        var oid = other.getAttribute('aria-controls');
        var op = oid ? document.getElementById(oid) : null;
        if (op) op.hidden = true;
      });
      if (expanded) {
        trigger.setAttribute('aria-expanded', 'false');
        if (panel) panel.hidden = true;
      } else {
        trigger.setAttribute('aria-expanded', 'true');
        if (panel) panel.hidden = false;
      }
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && navMobile && navMobile.classList.contains('is-open')) {
      closeMobileMenu();
    }
  });

  const mobileLinks = navMobile ? navMobile.querySelectorAll('.nav-link') : [];
  mobileLinks.forEach(function (link) {
    link.addEventListener('click', closeMobileMenu);
  });
  if (navMobile) {
    navMobile.addEventListener('click', function (e) {
      if (e.target.closest && e.target.closest('.header-actions .btn')) closeMobileMenu();
    });
  }

  const path = window.location.pathname || '';
  let page = path.split('/').pop() || 'index.html';
  if (page === '') page = 'index.html';
  document.querySelectorAll('.nav-desktop .nav-link[href], .nav-mobile .nav-link').forEach(function (link) {
    const href = (link.getAttribute('href') || '').split('#')[0].split('?')[0];
    const linkPage = href.split('/').pop() || '';
    const active = linkPage === page || (page === 'index.html' && (href === '/' || linkPage === 'index.html'));
    link.classList.toggle('active', active);
  });
  document.querySelectorAll('.nav-item--dropdown').forEach(function (item) {
    const trigger = item.querySelector('.nav-link--trigger');
    const hasActive = item.querySelector('.dropdown a.active');
    if (trigger && hasActive) trigger.classList.add('active');
  });

  function normalizeText(value) {
    return (value || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim();
  }

  function createNavbarSearch() {
    const navDesktop = document.querySelector('.nav-desktop');
    if (!navDesktop || navDesktop.querySelector('.nav-search')) return;

    const links = Array.from(document.querySelectorAll('.nav-desktop a[href], .nav-mobile a[href]'))
      .map(function (a) {
        return {
          href: (a.getAttribute('href') || '').trim(),
          label: (a.textContent || '').trim(),
          type: 'page',
          keywords: ''
        };
      })
      .filter(function (item) { return item.href && item.label; });

    const unique = [];
    const seen = {};
    links.forEach(function (item) {
      const key = item.href + '|' + item.label;
      if (!seen[key]) {
        seen[key] = true;
        unique.push(item);
      }
    });

    const searchable = unique.slice();
    const newsUrlPath = 'data/news.json';

    const form = document.createElement('form');
    form.className = 'nav-search';
    form.setAttribute('role', 'search');
    form.setAttribute('action', '#');
    form.setAttribute('autocomplete', 'off');

    const input = document.createElement('input');
    input.className = 'nav-search-input';
    input.type = 'search';
    input.placeholder = 'Search...';
    input.setAttribute('aria-label', 'Search pages');
    input.setAttribute('autocomplete', 'off');

    const btn = document.createElement('button');
    btn.className = 'nav-search-btn';
    btn.type = 'submit';
    btn.textContent = 'Search';
    btn.setAttribute('aria-label', 'Search');

    const results = document.createElement('div');
    results.className = 'nav-search-results';
    results.hidden = true;

    form.appendChild(input);
    form.appendChild(btn);
    form.appendChild(results);
    navDesktop.appendChild(form);

    fetch(newsUrlPath)
      .then(function (res) { return res.ok ? res.json() : []; })
      .then(function (data) {
        if (!Array.isArray(data)) return;
        data.forEach(function (item) {
          searchable.push({
            href: (item.url || 'lajme.html') + (item.id ? '#news-' + item.id : ''),
            label: (item.title || '').trim(),
            type: 'news',
            keywords: ((item.excerpt || '') + ' ' + (item.date || '')).trim()
          });
        });
      })
      .catch(function () {});

    var activeIndex = -1;
    var currentMatches = [];

    function scoreResult(q, entry) {
      var label = normalizeText(entry.label);
      var href = normalizeText(entry.href);
      var keywords = normalizeText(entry.keywords || '');
      if (!q) return 0;
      if (label === q) return 120;
      if (label.startsWith(q)) return 100;
      if (label.includes(q)) return 80;
      if (keywords.includes(q)) return 60;
      if (href.includes(q.replace(/\s+/g, '-'))) return 40;
      return 0;
    }

    function renderResults(matches) {
      results.innerHTML = '';
      activeIndex = -1;
      currentMatches = matches.slice(0, 8);
      if (!currentMatches.length) {
        const empty = document.createElement('div');
        empty.className = 'nav-search-empty';
        empty.textContent = 'No results';
        results.appendChild(empty);
        results.hidden = false;
        return;
      }
      currentMatches.forEach(function (item, idx) {
        const link = document.createElement('a');
        link.href = item.href;
        link.className = 'nav-search-result';
        link.setAttribute('data-result-index', String(idx));

        const type = document.createElement('span');
        type.className = 'nav-search-type';
        type.textContent = item.type === 'news' ? 'News' : 'Page';

        const title = document.createElement('span');
        title.className = 'nav-search-result-title';
        title.textContent = item.label;

        link.appendChild(type);
        link.appendChild(title);
        results.appendChild(link);
      });
      results.hidden = false;
    }

    function runSearch() {
      const q = normalizeText(input.value);
      if (!q) {
        results.hidden = true;
        results.innerHTML = '';
        activeIndex = -1;
        currentMatches = [];
        return [];
      }
      var ranked = searchable
        .map(function (item) { return { item: item, score: scoreResult(q, item) }; })
        .filter(function (x) { return x.score > 0; })
        .sort(function (a, b) { return b.score - a.score; })
        .map(function (x) { return x.item; });
      renderResults(ranked);
      return ranked;
    }

    function setActiveResult(index) {
      const items = results.querySelectorAll('.nav-search-result');
      items.forEach(function (el) { el.classList.remove('is-active'); });
      if (index >= 0 && index < items.length) {
        items[index].classList.add('is-active');
        activeIndex = index;
      } else {
        activeIndex = -1;
      }
    }

    input.addEventListener('input', runSearch);
    input.addEventListener('focus', function () {
      if (input.value.trim()) runSearch();
    });

    input.addEventListener('keydown', function (e) {
      if (results.hidden || !currentMatches.length) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        setActiveResult(Math.min(activeIndex + 1, currentMatches.length - 1));
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        setActiveResult(Math.max(activeIndex - 1, 0));
      } else if (e.key === 'Escape') {
        results.hidden = true;
      } else if (e.key === 'Enter' && activeIndex >= 0) {
        e.preventDefault();
        window.location.href = currentMatches[activeIndex].href;
      }
    });

    document.addEventListener('click', function (evt) {
      if (!form.contains(evt.target)) results.hidden = true;
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const matches = runSearch();
      if (!matches.length) {
        window.location.href = 'lajme.html';
        return;
      }
      if (activeIndex >= 0 && currentMatches[activeIndex]) {
        window.location.href = currentMatches[activeIndex].href;
      } else {
        window.location.href = matches[0].href;
      }
    });
  }

  createNavbarSearch();
})();
