// Manejo de sesión específico para Chrome en la PWA KASU
document.addEventListener('DOMContentLoaded', () => {
  const ua = navigator.userAgent || '';
  const isChrome = /Chrome/i.test(ua) && !/Edg\//i.test(ua) && !/OPR\//i.test(ua);
  if (!isChrome) return;

  const log = (...args) => console.debug('[KASU][ChromeSession]', ...args);
  const isOnPrincipal = () => window.location.pathname.toLowerCase().includes('/login/pwa_principal.php');
  const sessionUrl = '/login/php/session_check.php';

  async function checkSession() {
    try {
      const res = await fetch(sessionUrl, { credentials: 'include', cache: 'no-store' });
      if (!res.ok) {
        log('Respuesta HTTP no OK', res.status);
        return;
      }
      const data = await res.json();
      log('Ping sesión', data);

      if (data.session_active && data.user_logged) {
        if (!isOnPrincipal()) {
          window.location.href = '/login/Pwa_Principal.php';
        }
        return;
      }

      if (!data.session_active || !data.user_logged) {
        if (isOnPrincipal()) {
          window.location.href = '/login/index.php';
        }
      }
    } catch (err) {
      log('Error al consultar sesión', err);
    }
  }

  // Chequeo periódico
  setInterval(checkSession, 5000);
  checkSession();

  // Manejo de cache del navegador (bfcache)
  window.addEventListener('pageshow', (evt) => {
    if (evt.persisted) {
      log('pageshow desde caché, revalidando sesión');
      checkSession();
    }
  });
});
