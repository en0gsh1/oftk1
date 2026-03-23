/**
 * OFTK – Registry page: load physiotherapists from data/physiotherapists.json, search, table
 */
(function () {
  'use strict';

  const tbody = document.getElementById('registryTableBody');
  const searchInput = document.getElementById('registrySearch');
  const noResults = document.getElementById('registryNoResults');
  let allData = [];

  function getQueryFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return (params.get('q') || '').trim().toLowerCase();
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function renderRow(p) {
    const name = escapeHtml(p.name || '');
    const license = escapeHtml(p.license || '');
    const city = escapeHtml(p.city || '');
    const specialty = escapeHtml(p.specialty || '');
    const phone = escapeHtml(p.phone || '');
    const email = escapeHtml(p.email || '');
    return `<tr>
      <td><strong>${name}</strong></td>
      <td>${license}</td>
      <td>${city}</td>
      <td>${specialty}</td>
      <td>${phone} · <a href="mailto:${email}">${email}</a></td>
    </tr>`;
  }

  function filterData(query) {
    if (!query) return allData;
    const q = query.toLowerCase();
    return allData.filter(function (p) {
      const name = (p.name || '').toLowerCase();
      const city = (p.city || '').toLowerCase();
      const specialty = (p.specialty || '').toLowerCase();
      const license = (p.license || '').toLowerCase();
      return name.indexOf(q) !== -1 || city.indexOf(q) !== -1 || specialty.indexOf(q) !== -1 || license.indexOf(q) !== -1;
    });
  }

  function renderTable(list) {
    if (!tbody) return;
    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding: 2rem;">Nuk u gjet asnjë rezultat.</td></tr>';
      if (noResults) noResults.style.display = 'none';
      return;
    }
    tbody.innerHTML = list.map(renderRow).join('');
    if (noResults) noResults.style.display = 'none';
  }

  function runSearch() {
    const q = (searchInput ? searchInput.value.trim() : '') || getQueryFromUrl();
    if (searchInput) searchInput.value = q;
    const filtered = filterData(q.toLowerCase());
    renderTable(filtered);
  }

  if (searchInput) {
    searchInput.addEventListener('input', runSearch);
    searchInput.addEventListener('keyup', runSearch);
    const urlQ = getQueryFromUrl();
    if (urlQ) searchInput.value = urlQ;
    runSearch();
  }

  const fallback = [
    { id: '1', name: 'Arben Krasniqi', license: 'FZK-001', city: 'Prishtinë', specialty: 'Ortopedi, Sport', phone: '+383 XX XXX XXX', email: 'arben.k@example.com' },
    { id: '2', name: 'Valmira Berisha', license: 'FZK-002', city: 'Prishtinë', specialty: 'Neurologji, Rehabilitim', phone: '+383 XX XXX XXX', email: 'valmira.b@example.com' },
    { id: '3', name: 'Driton Hoxha', license: 'FZK-003', city: 'Pejë', specialty: 'Terapi Manuale', phone: '+383 XX XXX XXX', email: 'driton.h@example.com' }
  ];

  allData = fallback;
  renderTable(allData);

  fetch('data/physiotherapists.json')
    .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
    .then(function (data) {
      if (Array.isArray(data) && data.length) {
        allData = data;
        runSearch();
      }
    })
    .catch(function () {});
})();
