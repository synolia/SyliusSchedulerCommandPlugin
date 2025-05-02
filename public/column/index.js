function fireLog(e) {
  e.preventDefault();
  const url = e.currentTarget.getAttribute('data-href');
  fetch(url)
    .then(response => response.text())
    .then(data => {
      const logModal = document.getElementById('log-modal');
      const doc = new DOMParser().parseFromString(data, 'text/html');
      setHTMLWithScript('.modal-body', doc.querySelector('.page').innerHTML);
      var modal = new bootstrap.Modal(logModal);
      modal.show();
      logModal.addEventListener('hide.bs.modal', () => {
        for (let i = 0; i < 10000; i++) {
          window.clearInterval(i);
        }
      })
    })
}

function setHTMLWithScript(selector, html) {
  const container = document.querySelector(selector);
  container.innerHTML = html;

  const scripts = container.querySelectorAll("script");
  scripts.forEach(oldScript => {
    const newScript = document.createElement("script");
    if (oldScript.src) {
      newScript.src = oldScript.src;
    } else {
      newScript.textContent = oldScript.textContent;
    }
    oldScript.replaceWith(newScript);
  });
}