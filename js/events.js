/**
 * OFTK – Events page: load data/events.json and render event cards
 */
(function () {
  'use strict';

  const container = document.getElementById('eventsList');
  if (!container) return;

  const fallback = [
    { id: '1', day: '15', month: 'Mars 2025', title: 'Terapi Manuale', meta: 'Prishtinë', body: 'Trajnim i certifikuar.', link: 'kontakt.html' }
  ];

  function render(events) {
    const list = events || fallback;
    container.innerHTML = list.map(function (e) {
      return '<article class="event-card">' +
        '<div class="event-date"><span class="day">' + (e.day || '') + '</span> ' + (e.month || '') + '</div>' +
        '<div class="event-content">' +
        '<h3>' + (e.title || '') + '</h3>' +
        '<p class="meta">' + (e.meta || '') + '</p>' +
        '<p>' + (e.body || '') + '</p>' +
        '<a href="' + (e.link || 'kontakt.html') + '" class="btn btn-outline mt-1">Regjistrohu</a>' +
        '</div></article>';
    }).join('');
  }

  render(fallback);

  fetch('data/events.json')
    .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
    .then(function (data) {
      if (Array.isArray(data) && data.length) render(data);
    })
    .catch(function () {});
})();
