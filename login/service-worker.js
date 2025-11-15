// /login/service-worker.js
const CACHE_STATIC = 'kasu-static-v3';
const CACHE_RUNTIME = 'kasu-runtime-v1';
const OFFLINE_URL = '/login/offline.html';

const FILES_TO_CACHE = [
  OFFLINE_URL,
  '/login/assets/css/styles.min.css',
  '/login/assets/img/logoKasu.png',
  '/login/assets/img/kasu_logo.jpeg',
  '/login/assets/img/icon-152x152.png',
  '/login/assets/img/icon-192x192.png',
  '/login/assets/img/icon-512x512.png',
  '/login/Javascript/install.js'
];

const PRECACHE_PATHS = new Set(FILES_TO_CACHE);

self.addEventListener('install', (evt) => {
  evt.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_STATIC);
      await cache.addAll(FILES_TO_CACHE);
    })()
  );
  self.skipWaiting();
});

self.addEventListener('activate', (evt) => {
  evt.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.map((key) => {
          if (![CACHE_STATIC, CACHE_RUNTIME].includes(key)) {
            return caches.delete(key);
          }
          return undefined;
        })
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (evt) => {
  const { request } = evt;
  if (request.method !== 'GET') {
    return;
  }

  if (request.mode === 'navigate') {
    evt.respondWith(handleNavigation(request));
    return;
  }

  const url = new URL(request.url);
  if (url.origin === self.location.origin && PRECACHE_PATHS.has(url.pathname)) {
    evt.respondWith(handleStaticAsset(request, url));
    return;
  }

  evt.respondWith(handleRuntimeRequest(request));
});

async function handleNavigation(request) {
  try {
    const fresh = await fetch(request);
    const cache = await caches.open(CACHE_RUNTIME);
    cache.put(request, fresh.clone());
    return fresh;
  } catch (err) {
    const cached = await caches.match(request);
    return cached || caches.match(OFFLINE_URL);
  }
}

async function handleStaticAsset(request, url) {
  const cache = await caches.open(CACHE_STATIC);
  const cached = await cache.match(url.pathname);

  if (url.search) {
    try {
      const fresh = await fetch(request);
      cache.put(url.pathname, fresh.clone());
      return fresh;
    } catch (err) {
      return cached || caches.match(OFFLINE_URL);
    }
  }

  if (cached) {
    return cached;
  }

  try {
    const fresh = await fetch(request);
    cache.put(url.pathname, fresh.clone());
    return fresh;
  } catch (err) {
    return caches.match(OFFLINE_URL);
  }
}

async function handleRuntimeRequest(request) {
  const cache = await caches.open(CACHE_RUNTIME);
  const cached = await cache.match(request);

  try {
    const network = await fetch(request);
    if (network && (network.status === 200 || network.type === 'opaque')) {
      cache.put(request, network.clone());
    }
    return network;
  } catch (err) {
    if (cached) {
      return cached;
    }
    return Response.error();
  }
}
