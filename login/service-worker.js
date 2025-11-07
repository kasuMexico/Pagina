// /login/service-worker.js
const CACHE_STATIC = 'kasu-static-v2'; // antes v1
const CACHE_RUNTIME = 'kasu-runtime-v1';
const OFFLINE_URL = '/login/offline.html';

const FILES_TO_CACHE = [
  '/login/',
  '/login/index.php',
  '/login/offline.html',
  '/login/assets/css/styles.min.css',
  '/login/assets/img/kasu_logo.jpeg'
];

self.addEventListener('install', (evt) => {
  evt.waitUntil(
    caches.open(CACHE_STATIC).then(c => c.addAll(FILES_TO_CACHE))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (evt) => {
  evt.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.map(k => {
        if (![CACHE_STATIC, CACHE_RUNTIME].includes(k)) return caches.delete(k);
      }))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (evt) => {
  const req = evt.request;
  if (req.method !== 'GET') return;

  // Navegaciones: red -> cache -> offline
  if (req.mode === 'navigate') {
    evt.respondWith((async () => {
      try {
        const fresh = await fetch(req);
        const cache = await caches.open(CACHE_RUNTIME);
        cache.put(req, fresh.clone());
        return fresh;
      } catch (e) {
        const cached = await caches.match(req);
        return cached || caches.match(OFFLINE_URL);
      }
    })());
    return;
  }

  const url = new URL(req.url);
  const same = url.origin === self.location.origin;

  // Estáticos precache: cache-first
  if (same && FILES_TO_CACHE.includes(url.pathname)) {
    evt.respondWith(caches.match(req).then(c => c || fetch(req)));
    return;
  }

  // Otros: stale-while-revalidate
  evt.respondWith((async () => {
    const cache = await caches.open(CACHE_RUNTIME);
    const cached = await cache.match(req);
    const fetching = fetch(req).then(res => {
      if (res && (res.status === 200 || res.type === 'opaque')) {
        cache.put(req, res.clone());
      }
      return res;
    }).catch(() => cached);
    return cached || fetching;
  })());
});
