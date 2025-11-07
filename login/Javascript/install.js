// /login/Javascript/install.js
// Registra el Service Worker con scope /login/
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/login/service-worker.js', { scope: '/login/' });
}

let deferredPrompt = null;
const btn = document.getElementById('btnInstall');

// Estilo básico si no lo tienes en CSS
if (btn) {
  btn.style.cssText = 'display:none;position:fixed;left:12px;right:12px;bottom:12px;padding:12px 16px;border:0;border-radius:12px;font-size:16px;background:#2F3BA2;color:#fff;z-index:10000';
}

// Evento que habilita instalar
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  if (btn) btn.style.display = 'block';
});

// Click en instalar
btn?.addEventListener('click', async () => {
  if (!deferredPrompt) return;
  btn.disabled = true;
  deferredPrompt.prompt();
  await deferredPrompt.userChoice;
  deferredPrompt = null;
  btn.style.display = 'none';
});

// Oculta el botón si ya está instalada
const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
if (isStandalone && btn) btn.style.display = 'none';
