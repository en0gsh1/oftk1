/**
 * OFTK – Gallery: data/gallery.json me album/folder sipas aktiviteteve
 * Formate: { "albums": [ { "id", "title", "title_en?", "photos": [...] } ] }
 *         ose listë e sheshtë [ {...} ] (një album i vetëm) ose foto me "album" / "folder"
 */
(function () {
  'use strict';

  var container = document.getElementById('galleryRoot') || document.getElementById('galleryGrid');
  if (!container) return;

  /** Numri i fotove të dukshme për faqe (për album). */
  var PAGE_SIZE = 3;

  /** Foto sipas id të albumit (për pager pas render-it). */
  var galleryAlbumPhotos = Object.create(null);

  /** Hero në krye: një foto nga galeria, ndërron çdo 5 s. */
  var GALLERY_RANDOM_HERO_MS = 5000;
  var gallerySliderTimers = [];
  /** Lista e përzier e të gjitha fotove (cikël). */
  var galleryHeroOrder = [];
  /** Foto aktuale në hero (për lightbox). */
  var galleryCurrentHeroPhoto = null;

  function clearGalleryHeroTimers() {
    gallerySliderTimers.forEach(function (t) {
      clearInterval(t);
    });
    gallerySliderTimers = [];
  }

  function flattenAllPhotos(albums) {
    var out = [];
    albums.forEach(function (a) {
      if (!a.photos || !a.photos.length) return;
      a.photos.forEach(function (p) {
        if (p && String(p.image || '').trim()) out.push(p);
      });
    });
    return out;
  }

  function shuffleHeroOrder(pool) {
    var copy = pool.filter(function (p) {
      return p && String(p.image || '').trim();
    }).slice();
    var m = copy.length;
    while (m > 1) {
      m -= 1;
      var i = Math.floor(Math.random() * (m + 1));
      var t = copy[m];
      copy[m] = copy[i];
      copy[i] = t;
    }
    return copy;
  }

  function randomHeroAriaLabel() {
    return getLang() === 'en' ? 'Gallery highlights' : 'Theks nga galeria';
  }

  /** Një foto e madhe; klik për lightbox. */
  function buildSingleHeroHtml(p) {
    if (!p || !String(p.image || '').trim()) return '';
    var url = escapeAttr(p.image);
    var alt = escapeAttr(p.title || '');
    return (
      '<div class="gallery-single-hero" role="region" aria-label="' +
      escapeAttr(randomHeroAriaLabel()) +
      '">' +
      '<button type="button" class="gallery-single-hero__nav gallery-single-hero__nav--prev" aria-label="Foto paraprake">‹</button>' +
      '<button type="button" class="gallery-single-hero__zoom" aria-label="Zmadho foton">' +
      '<img class="gallery-single-hero__img" src="' +
      url +
      '" alt="' +
      alt +
      '" loading="lazy" decoding="async">' +
      '</button>' +
      '<button type="button" class="gallery-single-hero__nav gallery-single-hero__nav--next" aria-label="Foto pasardhëse">›</button>' +
      '</div>'
    );
  }

  function initRandomGalleryHero(root) {
    clearGalleryHeroTimers();
    var wrap = root.querySelector('.gallery-single-hero');
    if (!wrap) return;

    var order = galleryHeroOrder;
    var n = order.length;
    if (!n) return;

    var idx = 0;
    var img = wrap.querySelector('.gallery-single-hero__img');
    var timer = null;

    function showAt(i) {
      idx = ((i % n) + n) % n;
      var p = order[idx];
      galleryCurrentHeroPhoto = p;
      if (img && p) {
        img.src = p.image || '';
        img.alt = p.title || '';
      }
    }

    function nextSlide() {
      showAt(idx + 1);
    }

    function stopAutoplay() {
      if (timer !== null) {
        clearInterval(timer);
        var ix = gallerySliderTimers.indexOf(timer);
        if (ix >= 0) gallerySliderTimers.splice(ix, 1);
        timer = null;
      }
    }

    function startAutoplay() {
      if (n <= 1) return;
      stopAutoplay();
      timer = setInterval(nextSlide, GALLERY_RANDOM_HERO_MS);
      gallerySliderTimers.push(timer);
    }

    function restartAutoplay() {
      stopAutoplay();
      startAutoplay();
    }

    var prevBtn = wrap.querySelector('.gallery-single-hero__nav--prev');
    var nextBtn = wrap.querySelector('.gallery-single-hero__nav--next');

    showAt(0);

    if (n <= 1) {
      if (prevBtn) prevBtn.hidden = true;
      if (nextBtn) nextBtn.hidden = true;
    } else {
      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          showAt(idx - 1);
          restartAutoplay();
        });
      }
      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          showAt(idx + 1);
          restartAutoplay();
        });
      }
    }

    wrap.addEventListener('mouseenter', stopAutoplay);
    wrap.addEventListener('mouseleave', startAutoplay);

    startAutoplay();
  }

  var fallback = {
    albums: [
      {
        id: 'shembull',
        title: 'Aktivitete OFTK',
        title_en: 'OFTK activities',
        photos: [
          {
            id: '1',
            image: 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&q=80',
            title: 'Aktivitet OFTK',
            caption: 'Prishtinë'
          }
        ]
      }
    ]
  };

  function getLang() {
    try {
      var l = localStorage.getItem('oftk_lang');
      return l === 'en' || l === 'sq' ? l : 'sq';
    } catch (e) {
      return 'sq';
    }
  }

  function escapeHtml(s) {
    if (s == null || s === '') return '';
    var d = document.createElement('div');
    d.textContent = String(s);
    return d.innerHTML;
  }

  function escapeAttr(s) {
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;');
  }

  function albumHeading(album) {
    var lang = getLang();
    if (lang === 'en' && album.title_en) return album.title_en;
    return album.title || '';
  }

  function normalizeGalleryInput(data) {
    if (!data) return [];

    if (data.albums && Array.isArray(data.albums) && data.albums.length) {
      return data.albums.map(function (a) {
        return {
          id:
            String(a.id || '')
              .trim()
              .replace(/\s+/g, '-')
              .toLowerCase() || 'album-' + Math.random().toString(36).slice(2, 9),
          title: a.title || '',
          title_en: a.title_en || '',
          photos: Array.isArray(a.photos) ? a.photos.slice() : []
        };
      });
    }

    if (Array.isArray(data) && data.length) {
      var byKey = {};
      var order = [];
      data.forEach(function (p) {
        var k = (p.album || p.folder || '').trim();
        if (!k) k = '__single__';
        if (!byKey[k]) {
          byKey[k] = [];
          order.push(k);
        }
        byKey[k].push(p);
      });
      if (order.length === 1 && order[0] === '__single__') {
        return [
          {
            id: 'galeri',
            title: '',
            title_en: '',
            photos: data.slice()
          }
        ];
      }
      return order.map(function (k) {
        return {
          id: k.replace(/\s+/g, '-').toLowerCase(),
          title: k,
          title_en: k,
          photos: byKey[k]
        };
      });
    }

    return [];
  }

  function photosSliceHtml(photos, start, size) {
    return photos
      .slice(start, start + size)
      .map(function (p) {
        return renderPhotoCard(p);
      })
      .join('');
  }

  function renderPhotoCard(p) {
    var img = p.image || '';
    var title = p.title || '';
    return (
      '<article class="card gallery-card">' +
      '<button type="button" class="gallery-card-image-wrap" aria-label="Zmadho foton">' +
      '<img class="card-image" src="' +
      escapeAttr(img) +
      '" alt="' +
      escapeAttr(title) +
      '">' +
      '</button>' +
      '<div class="card-body">' +
      '<h3 class="card-title">' +
      escapeHtml(title) +
      '</h3>' +
      '<p>' +
      escapeHtml(p.caption || '') +
      '</p>' +
      '</div>' +
      '</article>'
    );
  }

  function updateAlbumPage(section, photos, page) {
    var totalPages = Math.max(1, Math.ceil(photos.length / PAGE_SIZE));
    page = Math.max(0, Math.min(totalPages - 1, page));
    section.setAttribute('data-gallery-page', String(page));
    var grid = section.querySelector('.gallery-album__grid');
    if (grid) {
      grid.innerHTML = photosSliceHtml(photos, page * PAGE_SIZE, PAGE_SIZE);
    }
    var prevBtn = section.querySelector('.gallery-pager__prev');
    var nextBtn = section.querySelector('.gallery-pager__next');
    if (prevBtn) prevBtn.disabled = page <= 0;
    if (nextBtn) nextBtn.disabled = page >= totalPages - 1;
  }

  /** Delegim: lightbox + butonat &lt; &gt; */
  function setupGalleryInteractions(root) {
    root.addEventListener('click', function (e) {
      var cardBtn = e.target.closest('.gallery-card-image-wrap');
      if (cardBtn && root.contains(cardBtn)) {
        var img = cardBtn.querySelector('img');
        if (img && img.src) {
          var lb = document.getElementById('galleryLightbox');
          if (lb) {
            lb.querySelector('img').src = img.src;
            lb.querySelector('img').alt = img.alt || '';
            lb.classList.add('is-open');
            lb.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
          }
        }
        return;
      }

      var singleZoom = e.target.closest('.gallery-single-hero__zoom');
      if (singleZoom && root.contains(singleZoom)) {
        var p = galleryCurrentHeroPhoto;
        var rurl = p && p.image ? String(p.image) : '';
        var ralt = p && p.title ? String(p.title) : '';
        if (rurl) {
          var lbs = document.getElementById('galleryLightbox');
          if (lbs) {
            lbs.querySelector('img').src = rurl;
            lbs.querySelector('img').alt = ralt;
            lbs.classList.add('is-open');
            lbs.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
          }
        }
        return;
      }

      var prevBtn = e.target.closest('.gallery-pager__prev');
      var nextBtn = e.target.closest('.gallery-pager__next');
      if (!prevBtn && !nextBtn) return;
      var section = e.target.closest('.gallery-album[data-gallery-album-id]');
      if (!section || !root.contains(section)) return;
      var id = section.getAttribute('data-gallery-album-id');
      var photos = galleryAlbumPhotos[id];
      if (!photos || photos.length === 0) return;
      var page = parseInt(section.getAttribute('data-gallery-page') || '0', 10);
      if (prevBtn) {
        updateAlbumPage(section, photos, page - 1);
      } else {
        updateAlbumPage(section, photos, page + 1);
      }
    });
  }

  function render(data) {
    var hadAlbumsArray = data && Array.isArray(data.albums);
    var albums = normalizeGalleryInput(data);
    if (!albums.length) {
      if (hadAlbumsArray) {
        clearGalleryHeroTimers();
        galleryHeroOrder = [];
        galleryCurrentHeroPhoto = null;
        container.innerHTML =
          '<p class="card-meta gallery-album__empty" style="grid-column:1/-1;">Nuk ka ende folderë në galeri.</p>';
        return;
      }
      albums = normalizeGalleryInput(fallback);
    }

    clearGalleryHeroTimers();

    galleryAlbumPhotos = Object.create(null);
    albums.forEach(function (a) {
      galleryAlbumPhotos[a.id] = a.photos.slice();
    });

    galleryHeroOrder = shuffleHeroOrder(flattenAllPhotos(albums));
    galleryCurrentHeroPhoto = galleryHeroOrder.length ? galleryHeroOrder[0] : null;
    var randomHeroHtml =
      galleryCurrentHeroPhoto ? buildSingleHeroHtml(galleryCurrentHeroPhoto) : '';

    var html = albums
      .map(function (album) {
        var sid = 'album-' + String(album.id || 'g').replace(/[^a-zA-Z0-9_-]/g, '-');
        var h = albumHeading(album);
        var head = '';
        var aria = '';
        if (h) {
          head = '<h2 class="gallery-album__title" id="' + sid + '-heading">' + escapeHtml(h) + '</h2>';
          aria = ' aria-labelledby="' + escapeAttr(sid + '-heading') + '"';
        } else {
          aria = ' aria-label="Foto galeria"';
        }
        var photos = album.photos;
        if (photos.length === 0) {
          return (
            '<section class="gallery-album"' +
            aria +
            '>' +
            head +
            '<div class="gallery-album__grid news-grid">' +
            '<p class="gallery-album__empty card-meta">Nuk ka ende foto në këtë album.</p>' +
            '</div></section>'
          );
        }
        var showPager = photos.length > PAGE_SIZE;
        var firstPageHtml = photosSliceHtml(photos, 0, PAGE_SIZE);
        var body;
        if (showPager) {
          body =
            '<div class="gallery-album__pager">' +
            '<button type="button" class="gallery-pager__btn gallery-pager__prev" aria-label="Foto paraprake">&lt;</button>' +
            '<div class="gallery-album__grid news-grid">' +
            firstPageHtml +
            '</div>' +
            '<button type="button" class="gallery-pager__btn gallery-pager__next" aria-label="Foto pasardhëse">&gt;</button>' +
            '</div>';
        } else {
          body = '<div class="gallery-album__grid news-grid">' + firstPageHtml + '</div>';
        }
        return (
          '<section class="gallery-album"' +
          aria +
          ' data-gallery-album-id="' +
          escapeAttr(album.id) +
          '" data-gallery-page="0">' +
          head +
          body +
          '</section>'
        );
      })
      .join('');

    container.innerHTML = randomHeroHtml + html;

    albums.forEach(function (album) {
      if (album.photos.length <= PAGE_SIZE) return;
      var sections = container.querySelectorAll('.gallery-album[data-gallery-album-id]');
      var section = null;
      for (var si = 0; si < sections.length; si++) {
        if (sections[si].getAttribute('data-gallery-album-id') === album.id) {
          section = sections[si];
          break;
        }
      }
      if (!section) return;
      updateAlbumPage(section, galleryAlbumPhotos[album.id], 0);
    });

    initRandomGalleryHero(container);
  }

  setupGalleryInteractions(container);
  render(fallback);

  function setupLightbox() {
    var lb = document.getElementById('galleryLightbox');
    if (!lb) return;
    function closeLightbox() {
      lb.classList.remove('is-open');
      document.body.style.overflow = '';
      lb.setAttribute('aria-hidden', 'true');
    }
    var closeBtn = lb.querySelector('.lightbox-close');
    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    lb.addEventListener('click', function (e) {
      if (e.target === lb) closeLightbox();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && lb.classList.contains('is-open')) closeLightbox();
    });
    var inner = lb.querySelector('.lightbox-inner');
    if (inner) inner.addEventListener('click', function (e) { e.stopPropagation(); });
  }
  setupLightbox();

  fetch('data/gallery.json')
    .then(function (r) {
      return r.ok ? r.json() : Promise.reject();
    })
    .then(function (data) {
      if (data && (data.albums || Array.isArray(data))) render(data);
    })
    .catch(function () {});
})();
