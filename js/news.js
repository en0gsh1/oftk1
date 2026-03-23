/**
 * OFTK – News: load data/news.json; lista e lajmeve (lajme.html) dhe hero në ballinë
 */
(function () {
  'use strict';

  const grid = document.getElementById('newsGrid');
  const heroMainNewsInner = document.getElementById('heroMainNewsInner');
  const homeExtraNewsGrid = document.getElementById('homeExtraNewsGrid');
  const heroExtraNewsItem = document.getElementById('heroExtraNewsItem');
  const heroPrev = document.getElementById('heroPrev');
  const heroNext = document.getElementById('heroNext');
  var heroRotateTimer = null;
  var extraRotateTimer = null;
  var heroBound = false;
  var heroItems = [];
  var heroIndex = 0;

  var linkReadMore = 'Lexo më shumë →';

  function setReadMoreLabel(cb) {
    var lang = (typeof localStorage !== 'undefined' && localStorage.getItem('oftk_lang')) || 'sq';
    if (lang !== 'sq' && lang !== 'en') lang = 'sq';
    var script = document.querySelector('script[src*="i18n.js"]');
    var base = script ? script.src.replace(/\/js\/i18n\.js.*$/, '') : '';
    fetch((base || '') + '/lang/' + lang + '.json')
      .then(function (r) { return r.ok ? r.json() : {}; })
      .then(function (t) {
        linkReadMore = (t && t['link.read_more']) ? t['link.read_more'] : linkReadMore;
        if (cb) cb();
      })
      .catch(function () { if (cb) cb(); });
  }

  const fallback = [
    { id: '1', published: '2025-02-21', title: 'Takim vjetor i Odës – 2025', date: '21 Shkurt 2025', excerpt: 'Ftohen të gjithë anëtarët në takimin vjetor që mbahet në Prishtinë.', image: 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&q=80', url: 'lajme.html#1' },
    { id: '2', published: '2025-02-15', title: 'Trajnim i ri për Terapi Manuale', date: '15 Shkurt 2025', excerpt: 'Trajnim i certifikuar për teknikat e terapisë manuale.', image: 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=600&q=80', url: 'lajme.html#2' },
    { id: '3', published: '2025-02-01', title: 'Përditësimi i Regjistrit', date: '1 Shkurt 2025', excerpt: 'Regjistri zyrtar është përditësuar.', image: 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=600&q=80', url: 'lajme.html#3' }
  ];

  /** Muajt për `date` tekst (Shqip / Anglisht) kur s'ka fushë `published` */
  var NEWS_MONTHS = {
    janar: 0, january: 0, jan: 0,
    shkurt: 1, february: 1, feb: 1,
    mars: 2, march: 2, mar: 2,
    prill: 3, april: 3, apr: 3,
    maj: 4, may: 4,
    qershor: 5, june: 5, jun: 5,
    korrik: 6, july: 6, jul: 6,
    gusht: 7, august: 7, aug: 7,
    shtator: 8, september: 8, sep: 8, sept: 8,
    tetor: 9, october: 9, oct: 9,
    nentor: 10, november: 10, nov: 10,
    dhjetor: 11, december: 11, dec: 11
  };

  function normalizeNewsMonthToken(str) {
    return (str || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/ë/g, 'e');
  }

  function parseNewsDisplayDate(dateStr) {
    if (!dateStr || typeof dateStr !== 'string') return 0;
    var m = dateStr.trim().match(/^(\d{1,2})\s+(.+?)\s+(\d{4})$/);
    if (!m) return 0;
    var day = parseInt(m[1], 10);
    var monKey = normalizeNewsMonthToken(m[2]);
    var year = parseInt(m[3], 10);
    var month = NEWS_MONTHS[monKey];
    if (month === undefined || day < 1 || day > 31) return 0;
    var d = new Date(year, month, day);
    return isNaN(d.getTime()) ? 0 : d.getTime();
  }

  /** Timestamp për renditje: `published` / `sortDate` (ISO), përndryshe `date` tekst */
  function getNewsSortTime(item) {
    if (!item) return 0;
    var iso = item.published || item.sortDate || item.dateIso;
    if (iso && typeof iso === 'string') {
      var t = Date.parse(iso.length <= 10 ? iso + 'T12:00:00' : iso);
      if (!isNaN(t)) return t;
    }
    return parseNewsDisplayDate(item.date);
  }

  function sortNewsItems(items) {
    if (!Array.isArray(items)) return [];
    return items.slice().sort(function (a, b) {
      return getNewsSortTime(b) - getNewsSortTime(a);
    });
  }

  function newsUrl(item) {
    const base = (item.url || 'lajme.html').split('#')[0];
    const id = item.id ? 'news-' + item.id : '';
    return id ? base + '#' + id : base;
  }

  function renderItem(item) {
    const img = item.image || '';
    const date = item.date || '';
    const title = item.title || '';
    const excerpt = item.excerpt || '';
    const url = newsUrl(item);
    const id = item.id ? ' id="news-' + item.id + '"' : '';
    return '<article class="card"' + id + '>\n' +
      '  <img class="card-image" src="' + img + '" alt="">\n' +
      '  <div class="card-body">\n' +
      '    <span class="card-meta">' + date + '</span>\n' +
      '    <h3 class="card-title">' + title + '</h3>\n' +
      '    <p>' + excerpt + '</p>\n' +
      '    <a href="' + url + '" class="link">' + linkReadMore + '</a>\n' +
      '  </div>\n' +
      '</article>';
  }

  function renderHeroMainNews(item) {
    const img = item.image || '';
    const date = item.date || '';
    const title = item.title || '';
    const excerpt = item.excerpt || '';
    const url = newsUrl(item);
    var bg = img
      ? 'style="background-image:url(\'' + img.replace(/'/g, '&#39;') + '\')"'
      : '';
    return '<article class="hero-news-slide">' +
      '<div class="hero-news-overlay" aria-hidden="true"></div>' +
      '<div class="hero-image-bg" ' + bg + ' aria-hidden="true"></div>' +
      '<div class="hero-news-content-wrap">' +
      '<div class="hero-news-content">' +
      '<span class="hero-news-kicker">' + date + '</span>' +
      '<h1 class="hero-news-title">' + title + '</h1>' +
      '<p class="hero-news-excerpt">' + excerpt + '</p>' +
      '<a href="' + url + '" class="btn btn-white">' + linkReadMore + '</a>' +
      '</div>' +
      '</div>' +
      '</article>';
  }

  function showNews(items) {
    if (!Array.isArray(items) || !items.length) return;
    var sorted = sortNewsItems(items);
    if (grid) grid.innerHTML = sorted.map(renderItem).join('');
  }

  function showHeroMainNewsAt(items, index) {
    if (!heroMainNewsInner || !Array.isArray(items) || !items.length) return 0;
    var i = Math.max(0, Math.min(items.length - 1, index));
    heroMainNewsInner.innerHTML = renderHeroMainNews(items[i]);
    return i;
  }

  function moveHero(step) {
    if (!Array.isArray(heroItems) || !heroItems.length) return;
    heroIndex = (heroIndex + step + heroItems.length) % heroItems.length;
    showHeroMainNewsAt(heroItems, heroIndex);
  }

  function startHeroRotation(items) {
    if (!heroMainNewsInner || !Array.isArray(items) || !items.length) return;
    if (heroRotateTimer) clearInterval(heroRotateTimer);
    heroItems = sortNewsItems(items);
    heroIndex = 0;
    heroIndex = showHeroMainNewsAt(heroItems, heroIndex);
    if (!heroBound) {
      if (heroPrev) {
        heroPrev.addEventListener('click', function () {
          moveHero(-1);
        });
      }
      if (heroNext) {
        heroNext.addEventListener('click', function () {
          moveHero(1);
        });
      }
      heroBound = true;
    }
    heroRotateTimer = setInterval(function () {
      moveHero(-1);
    }, 5000);
  }

  function renderExtraItemInner(item) {
    const date = item.date || '';
    const title = item.title || '';
    const url = newsUrl(item);

    return '' +
      '<span class="hero-extra-news-meta">' + date + '</span>\n' +
      '  <h3 class="hero-extra-news-title">' + title + '</h3>\n' +
      '  <a href="' + url + '" class="hero-extra-news-link link">' + linkReadMore + '</a>';
  }

  function showExtraNewsAt(items, index, animate) {
    if (!heroExtraNewsItem || !Array.isArray(items) || !items.length) return;
    if (items.length < 2) return;

    /* Lista e renditur: [0]=më i ri (hero); mini shfaq 1…length-1 */
    var minIndex = 1;
    var maxIndex = items.length - 1;
    var i = Math.max(minIndex, Math.min(maxIndex, index));
    var html = renderExtraItemInner(items[i]);

    if (animate) {
      heroExtraNewsItem.classList.add('is-updating');
      setTimeout(function () {
        heroExtraNewsItem.innerHTML = html;
        heroExtraNewsItem.classList.remove('is-updating');
      }, 250);
    } else {
      heroExtraNewsItem.innerHTML = html;
    }
  }

  function startExtraRotation(items) {
    if (!homeExtraNewsGrid || !heroExtraNewsItem || !Array.isArray(items) || !items.length) return;
    if (items.length < 2) return;

    if (extraRotateTimer) clearInterval(extraRotateTimer);

    var sorted = sortNewsItems(items);
    var idx = 1;
    showExtraNewsAt(sorted, idx, false);

    extraRotateTimer = setInterval(function () {
      idx += 1;
      if (idx >= sorted.length) idx = 1;
      showExtraNewsAt(sorted, idx, true);
    }, 7500);
  }

  setReadMoreLabel(function () {
    if (grid) showNews(fallback);
    if (heroMainNewsInner) startHeroRotation(fallback);
    startExtraRotation(fallback);
  });

  fetch('data/news.json')
    .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
    .then(function (data) {
      if (!Array.isArray(data) || !data.length) return;
      setReadMoreLabel(function () {
        showNews(data);
        startHeroRotation(data);
        startExtraRotation(data);
        scrollToNewsHash();
      });
    })
    .catch(function () {
      scrollToNewsHash();
    });

  function scrollToNewsHash() {
    var hash = window.location.hash;
    if (!hash || hash.indexOf('news-') !== 0) return;
    var el = document.getElementById(hash.slice(1));
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  if (grid && window.location.hash) setTimeout(scrollToNewsHash, 300);
})();
