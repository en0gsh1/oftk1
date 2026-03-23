/**
 * Login form: client-side demo (no real auth backend)
 */
(function () {
  'use strict';

  var form = document.getElementById('loginForm');
  var messageEl = document.getElementById('loginMessage');
  if (!form || !messageEl) return;

  function showMessage(text, type) {
    messageEl.textContent = text;
    messageEl.className = 'alert alert-' + (type === 'error' ? 'error' : 'success');
    messageEl.style.display = 'block';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var email = (document.getElementById('loginEmail') || {}).value || '';
    var password = (document.getElementById('loginPassword') || {}).value || '';

    if (!email || !password) {
      showMessage('Ju lutem plotësoni email-in dhe fjalëkalimin.', 'error');
      return;
    }

    showMessage('Sistemi i hyrjes për anëtarët do të lidhet me një server në të ardhmen. Për akses anëtarësie, na kontaktoni në info@odafizioterapeuteve-ks.org.', 'success');
  });
})();
