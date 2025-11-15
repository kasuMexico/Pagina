// /login/Javascript/swipe-nav.js
(() => {
  const THRESHOLD = 60;      // mínimo de px horizontales
  const MAX_OFF_AXIS = 80;   // tolerancia vertical
  const MAX_TIME = 700;      // ms para considerar swipe

  let startX = 0;
  let startY = 0;
  let startTime = 0;
  let isTracking = false;

  const interactiveSelector = 'input, textarea, select, button, a, [data-no-swipe]';

  function getNavItems() {
    const buttons = Array.from(document.querySelectorAll('#Menu .BtnMenu'));
    return buttons.filter(Boolean);
  }

  function findActiveIndex(items) {
    if (!items.length) return -1;
    let idx = items.findIndex((el) => el.getAttribute('aria-current') === 'page');
    if (idx !== -1) return idx;
    return items.findIndex((el) => !el.hasAttribute('href'));
  }

  function navigate(delta) {
    const items = getNavItems();
    if (!items.length) return;
    const activeIdx = findActiveIndex(items);
    if (activeIdx === -1) return;

    let idx = activeIdx + delta;
    while (idx >= 0 && idx < items.length) {
      const candidate = items[idx];
      const href = candidate.getAttribute('href');
      if (href) {
        window.location.assign(href);
        break;
      }
      idx += delta;
    }
  }

  function onTouchStart(evt) {
    if (evt.touches.length !== 1) return;
    if (evt.target.closest(interactiveSelector)) {
      isTracking = false;
      return;
    }
    const touch = evt.touches[0];
    startX = touch.clientX;
    startY = touch.clientY;
    startTime = Date.now();
    isTracking = true;
  }

  function onTouchEnd(evt) {
    if (!isTracking) return;
    const touch = evt.changedTouches[0];
    const dx = touch.clientX - startX;
    const dy = touch.clientY - startY;
    const dt = Date.now() - startTime;
    isTracking = false;

    if (Math.abs(dy) > MAX_OFF_AXIS) return;
    if (dt > MAX_TIME) return;

    if (dx <= -THRESHOLD) {
      navigate(1); // swipe left -> siguiente pantalla
    } else if (dx >= THRESHOLD) {
      navigate(-1); // swipe right -> pantalla anterior
    }
  }

  document.addEventListener('touchstart', onTouchStart, { passive: true });
  document.addEventListener('touchend', onTouchEnd, { passive: true });
})();
