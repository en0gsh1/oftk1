/**
 * OFTK – Documents page: load data/documents.json and render download list
 */
(function () {
  'use strict';

  const container = document.getElementById('docList');
  if (!container) return;

  const icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
  const fallback = [
    { id: '1', title: 'Formulari i Aplikimit për Anëtarësim', meta: 'PDF', url: 'documents/formular-anetaresimi.pdf' },
    { id: '2', title: 'Kodiksi i Etikës', meta: 'PDF', url: 'documents/kodiksi-etikes.pdf' }
  ];

  function render(docs) {
    const list = docs || fallback;
    container.innerHTML = list.map(function (d) {
      const title = d.title || '';
      const meta = d.meta || '';
      const url = d.url || '#';
      return `<div class="doc-item">
        <div class="doc-item-icon">${icon}</div>
        <div class="doc-item-content">
          <div class="doc-item-title">${title}</div>
          <div class="doc-item-meta">${meta}</div>
        </div>
        <a href="${url}" class="doc-item-download" download>Shkarko PDF</a>
      </div>`;
    }).join('');
  }

  render(fallback);

  fetch('data/documents.json')
    .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
    .then(function (data) {
      if (Array.isArray(data) && data.length) render(data);
    })
    .catch(function () {});
})();
