/**
 * OFTK – i18n: Shqip & English (US).
 * Loads lang/{sq|en}.json and replaces [data-i18n], [data-i18n-href], [data-i18n-src], [data-i18n-placeholder], [data-i18n-title].
 * Zgjedhësi i gjuhës: dropdown (trigger + lista).
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'oftk_lang';
  const DEFAULT_LANG = 'sq';
  const LANGS = { sq: 'Shqip', en: 'English (US)' };
  /** Etiketa në trigger dhe në opsionet e dropdown-it */
  const LANG_BUTTON_LABEL = { sq: 'Shqip', en: 'English' };
  const SUPPORTED_LANGS = ['sq', 'en'];

  function getLang() {
    try {
      const l = localStorage.getItem(STORAGE_KEY);
      if (l === 'sr') {
        localStorage.setItem(STORAGE_KEY, DEFAULT_LANG);
        return DEFAULT_LANG;
      }
      return (l === 'sq' || l === 'en') ? l : DEFAULT_LANG;
    } catch (e) {
      return DEFAULT_LANG;
    }
  }

  function setLang(lang) {
    try {
      localStorage.setItem(STORAGE_KEY, lang);
    } catch (e) {}
    window.location.reload();
  }

  function applyTranslations(t) {
    if (!t) return;
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      const key = el.getAttribute('data-i18n');
      if (t[key] !== undefined) el.textContent = t[key];
    });
    document.querySelectorAll('[data-i18n-href]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-href');
      if (t[key] !== undefined) el.setAttribute('href', t[key]);
    });
    document.querySelectorAll('[data-i18n-src]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-src');
      if (t[key] !== undefined) el.setAttribute('src', t[key]);
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-placeholder');
      if (t[key] !== undefined) el.placeholder = t[key];
    });
    document.querySelectorAll('[data-i18n-title]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-title');
      if (t[key] !== undefined) el.title = t[key];
    });
    const titleKey = document.documentElement.getAttribute('data-i18n-page-title');
    if (titleKey && t[titleKey]) document.title = t[titleKey] + ' | OFTK';
  }

  function renderSwitcher(container, current) {
    if (!container) return;
    container.innerHTML = '';
    var wrap = document.createElement('div');
    wrap.className = 'lang-switcher lang-switcher--dropdown';

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'lang-switcher__trigger';
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-label', 'Zgjidhni gjuhën');

    var currentSpan = document.createElement('span');
    currentSpan.className = 'lang-switcher__current';
    currentSpan.textContent = LANG_BUTTON_LABEL[current] || LANGS[current] || current;

    var caret = document.createElement('span');
    caret.className = 'lang-switcher__caret';
    caret.setAttribute('aria-hidden', 'true');
    caret.textContent = '\u25BE';

    trigger.appendChild(currentSpan);
    trigger.appendChild(caret);

    var menu = document.createElement('div');
    menu.className = 'lang-switcher__menu';
    menu.id = 'langSwitcherMenu';
    menu.setAttribute('role', 'listbox');
    menu.setAttribute('aria-label', 'Gjuhët');
    menu.hidden = true;

    trigger.setAttribute('aria-controls', menu.id);

    function closeMenu() {
      wrap.classList.remove('is-open');
      menu.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
    }
    function openMenu() {
      wrap.classList.add('is-open');
      menu.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
    }
    function toggleMenu() {
      if (menu.hidden) openMenu();
      else closeMenu();
    }

    SUPPORTED_LANGS.forEach(function (code) {
      var opt = document.createElement('button');
      opt.type = 'button';
      opt.className = 'lang-switcher__option' + (code === current ? ' is-active' : '');
      opt.setAttribute('role', 'option');
      opt.setAttribute('aria-selected', code === current ? 'true' : 'false');
      opt.setAttribute('data-lang', code);
      opt.setAttribute('lang', code === 'sq' ? 'sq' : 'en');
      opt.textContent = LANG_BUTTON_LABEL[code] || LANGS[code] || code;
      opt.addEventListener('click', function (e) {
        e.stopPropagation();
        closeMenu();
        if (code !== current) setLang(code);
      });
      menu.appendChild(opt);
    });

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      toggleMenu();
    });

    wrap.appendChild(trigger);
    wrap.appendChild(menu);
    container.appendChild(wrap);

    document.addEventListener('click', function onDocClick(e) {
      if (!wrap.contains(e.target)) closeMenu();
    });
    document.addEventListener('keydown', function onEsc(e) {
      if (e.key === 'Escape' && !menu.hidden) {
        closeMenu();
        trigger.focus();
      }
    });
  }

  const lang = getLang();
  document.documentElement.setAttribute('lang', lang === 'sq' ? 'sq' : 'en');

  const base = document.querySelector('script[src*="i18n.js"]');
  const basePath = base ? base.src.replace(/\/js\/i18n\.js.*$/, '') : '';

  fetch((basePath || '') + '/lang/' + lang + '.json')
    .then(function (r) { return r.ok ? r.json() : {}; })
    .then(function (t) {
      applyTranslations(t);
      var container = document.getElementById('langSwitcher');
      var headerActions = document.querySelector('.site-header .header-actions');
      if (headerActions) {
        if (!container) {
          container = document.createElement('div');
          container.id = 'langSwitcher';
        }
        headerActions.prepend(container);
      }
      if (container) renderSwitcher(container, lang);
      if (typeof window.__oftk_relocateHeader === 'function') window.__oftk_relocateHeader();
    })
    .catch(function () {});
})();
