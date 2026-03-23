/**
 * OFTK – Contact form: validation and submit to send-contact.php (info@oftk-ks.org)
 */
(function () {
  'use strict';

  const form = document.getElementById('contactForm');
  const alertEl = document.getElementById('contactAlert');
  if (!form || !alertEl) return;

  function showAlert(message, type, options) {
    alertEl.textContent = '';
    alertEl.className = 'alert alert-' + (type === 'error' ? 'error' : 'success');
    alertEl.appendChild(document.createTextNode(message));
    if (options && options.mailto) {
      alertEl.appendChild(document.createTextNode(' '));
      const link = document.createElement('a');
      link.href = options.mailto;
      link.textContent = 'Dërgo me email';
      link.setAttribute('target', '_blank');
      link.rel = 'noopener';
      alertEl.appendChild(link);
    }
    alertEl.style.display = 'block';
    alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function hideAlert() {
    alertEl.style.display = 'none';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    hideAlert();

    const nameInput = document.getElementById('contactName');
    const emailInput = document.getElementById('contactEmail');
    const subjectInput = document.getElementById('contactSubject');
    const messageInput = document.getElementById('contactMessage');
    const name = (nameInput && nameInput.value) ? nameInput.value.trim() : '';
    const email = (emailInput && emailInput.value) ? emailInput.value.trim() : '';
    const subject = (subjectInput && subjectInput.value) ? subjectInput.value.trim() : '';
    const message = (messageInput && messageInput.value) ? messageInput.value.trim() : '';

    if (!name) {
      showAlert('Ju lutem vendosni emrin dhe mbiemrin.', 'error');
      return;
    }
    if (!email) {
      showAlert('Ju lutem vendosni email-in.', 'error');
      return;
    }
    if (!message) {
      showAlert('Ju lutem shkruani mesazhin.', 'error');
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Duke dërguar...';
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('subject', subject);
    formData.append('message', message);

    const formAction = form.getAttribute('action') || 'send-contact.php';
    fetch(formAction, {
      method: 'POST',
      body: formData,
    })
      .then(function (res) {
        return res.json().then(function (data) {
          if (!data.ok) {
            throw new Error(data.message || 'Diçka shkoi keq.');
          }
          return data;
        }).catch(function (err) {
          if (err.message) throw err;
          throw new Error('Përgjigje e pavlefshme nga serveri.');
        });
      })
      .then(function (data) {
        showAlert(data.message || 'Mesazhi u dërgua.', 'success');
        form.reset();
      })
      .catch(function (err) {
        const bodyText = 'Emri: ' + name + '\nEmail: ' + email + '\n\n' + message;
        const mailto = 'mailto:info@oftk-ks.org?subject=' + encodeURIComponent(subject || 'Kontakt nga faqja OFTK') + '&body=' + encodeURIComponent(bodyText);
        showAlert(
          err.message || 'Mesazhi nuk u dërgua. Ju lutem provoni me email: info@oftk-ks.org ose tel: +383 45 460 551.',
          'error',
          { mailto: mailto }
        );
      })
      .finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      });
  });
})();
