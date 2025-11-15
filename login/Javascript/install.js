// /login/Javascript/install.js
(() => {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/login/service-worker.js', { scope: '/login/' })
      .catch((err) => console.error('SW register failed', err));
  }

  let deferredPrompt = null;
  const btn = document.getElementById('btnInstall');

  const hideButton = () => {
    if (!btn) return;
    btn.disabled = false;
    btn.classList.remove('is-visible');
  };

  const showButton = () => {
    if (!btn) return;
    btn.disabled = false;
    btn.classList.add('is-visible');
  };

  if (btn) {
    btn.classList.add('btn-install');
  }

  window.addEventListener('beforeinstallprompt', (evt) => {
    evt.preventDefault();
    deferredPrompt = evt;
    showButton();
  });

  btn?.addEventListener('click', async () => {
    if (!deferredPrompt) {
      return;
    }
    btn.disabled = true;
    deferredPrompt.prompt();
    await deferredPrompt.userChoice;
    deferredPrompt = null;
    hideButton();
  });

  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    hideButton();
  });

  const isStandalone = window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;
  if (isStandalone) {
    hideButton();
  }
})();
