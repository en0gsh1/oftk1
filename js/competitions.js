/**
 * OFTK – Competitions page: load data/competitions.json and render list
 */
(function () {
  'use strict';

  const container = document.getElementById('competitionsList');
  if (!container) return;

  const fallback = [
    { id: '1', title: 'Konkurs', date: '', excerpt: 'Kontrolloni faqen për detaje.', link: 'kontakt.html' }
  ];

  function render(items) {
    const list = items || fallback;
    container.innerHTML = list.map(function (c) {
      const title = c.title || '';
      const date = c.date || '';
      const excerpt = c.excerpt || '';
      const link = c.link || 'kontakt.html';
      return `<div class="doc-item">
        <div class="doc-item-content">
          <div class="doc-item-title">${title}</div>
          <div class="doc-item-meta">${date}</div>
          <p>${excerpt}</p>
        </div>
        <a href="${link}" class="doc-item-download">Shiko</a>
      </div>`;
    }).join('');
  }

  render(fallback);

  fetch('data/competitions.json')
    .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
    .then(function (data) {
      if (Array.isArray(data) && data.length) render(data);
    })
    .catch(function () {});
})();
