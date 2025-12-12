// /login/service-worker.js
'use strict';

const CACHE_STATIC  = 'kasu-static-v5';
const CACHE_RUNTIME = 'kasu-runtime-v3';
const OFFLINE_URL   = '/login/offline.html';

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

// Usamos solo las rutas (path) para compararlas con url.pathname
const PRECACHE_PATHS = new Set(FILES_TO_CACHE);

// ================== INSTALL ==================
self.addEventListener('install', (evt) => {
  evt.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_STATIC);
      await cache.addAll(FILES_TO_CACHE);
    })()
  );
  self.skipWaiting();
});

// ================== ACTIVATE =================
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

// =================== FETCH ===================
self.addEventListener('fetch', (evt) => {
  const request = evt.request;
  const url = new URL(request.url);

  // EXCEPCIÓN CRÍTICA: NO INTERCEPTAR PDFs ni archivos descargables
  // Dejar que el navegador maneje las descargas directamente
  if (isDownloadableFile(request, url)) {
    return; // Sale sin interceptar
  }

  // Solo GET; no interceptar POST/PUT/DELETE (formularios, login, etc.)
  if (request.method !== 'GET') {
    return;
  }

  // Solo http/https. Ignora chrome-extension://, chrome://, data:, etc.
  if (url.protocol !== 'http:' && url.protocol !== 'https:') {
    return;
  }

  // No interceptar nada del backend de login
  if (url.pathname.startsWith('/login/php/')) {
    return;
  }

  // No interceptar rutas de generación de PDF
  if (url.pathname.includes('Cotizacion_pdf.php') || 
      url.pathname.includes('Generar_PDF')) {
    return;
  }

  // No interceptar archivos en directorio DATES
  if (url.pathname.includes('/DATES/')) {
    return;
  }

  // Navegaciones (documentos) solo de mismo origen
  if (request.mode === 'navigate') {
    if (url.origin === self.location.origin) {
      evt.respondWith(handleNavigation(request));
    }
    return;
  }

  // Archivos estáticos precacheados (mismo origen)
  if (url.origin === self.location.origin && PRECACHE_PATHS.has(url.pathname)) {
    evt.respondWith(handleStaticAsset(request, url));
    return;
  }

  // Resto: estrategia runtime (solo http/https, ya filtrado arriba)
  evt.respondWith(handleRuntimeRequest(request));
});

// =============== FUNCIONES AUXILIARES =================

// Determina si es un archivo descargable
function isDownloadableFile(request, url) {
  // Verificar por Content-Disposition header (si ya tenemos la respuesta)
  if (request.headers && request.headers.get('Accept')) {
    const accept = request.headers.get('Accept');
    if (accept.includes('application/pdf') || accept.includes('application/octet-stream')) {
      return true;
    }
  }

  // Verificar por extensión de archivo o ruta
  const pathname = url.pathname.toLowerCase();
  const downloadableExtensions = [
    '.pdf', '.xlsx', '.xls', '.doc', '.docx', '.zip', '.rar', 
    '.7z', '.txt', '.csv', '.jpg', '.jpeg', '.png', '.gif'
  ];

  // Verificar si la ruta contiene indicadores de descarga
  if (pathname.includes('/download/') || 
      pathname.includes('/export/') || 
      pathname.includes('/generar/') ||
      pathname.includes('/cotizacion') ||
      pathname.includes('descargar')) {
    return true;
  }

  // Verificar por extensión
  for (const ext of downloadableExtensions) {
    if (pathname.endsWith(ext)) {
      return true;
    }
  }

  // Verificar por parámetros que indiquen descarga
  const searchParams = url.searchParams;
  if (searchParams.has('download') || 
      searchParams.has('export') || 
      searchParams.has('pdf') ||
      searchParams.has('DescargaPres')) {
    return true;
  }

  return false;
}

async function handleNavigation(request) {
  const cache = await caches.open(CACHE_RUNTIME);

  try {
    const response = await fetch(request);
    const url = new URL(request.url);

    // Doble filtro de seguridad: solo cachear http/https de mismo origen
    // Y NO cachear respuestas con headers de descarga
    const contentType = response.headers.get('content-type');
    const contentDisposition = response.headers.get('content-disposition');
    
    const shouldCache = (
      (url.protocol === 'http:' || url.protocol === 'https:') &&
      url.origin === self.location.origin &&
      response &&
      response.status === 200 &&
      contentType &&
      !contentType.includes('application/pdf') &&
      !contentType.includes('application/octet-stream') &&
      (!contentDisposition || !contentDisposition.includes('attachment'))
    );

    if (shouldCache) {
      await cache.put(request, response.clone());
    }

    return response;
  } catch (err) {
    const cached = await cache.match(request);
    if (cached) {
      return cached;
    }
    return caches.match(OFFLINE_URL);
  }
}

async function handleStaticAsset(request, url) {
  const cache  = await caches.open(CACHE_STATIC);
  const cached = await cache.match(url.pathname);

  // Si trae querystring (?v=123), intentamos red primero y caemos a cache
  if (url.search) {
    try {
      const response = await fetch(request);
      if (response && response.status === 200) {
        await cache.put(url.pathname, response.clone());
      }
      return response;
    } catch (err) {
      return cached || caches.match(OFFLINE_URL);
    }
  }

  if (cached) {
    return cached;
  }

  try {
    const response = await fetch(request);
    if (response && response.status === 200) {
      await cache.put(url.pathname, response.clone());
    }
    return response;
  } catch (err) {
    return caches.match(OFFLINE_URL);
  }
}

async function handleRuntimeRequest(request) {
  const cache  = await caches.open(CACHE_RUNTIME);
  const cached = await cache.match(request);

  try {
    const response = await fetch(request);
    const url = new URL(request.url);

    // Verificar si es un archivo descargable antes de cachear
    const contentType = response.headers.get('content-type');
    const contentDisposition = response.headers.get('content-disposition');
    
    const isDownloadable = (
      contentType && (
        contentType.includes('application/pdf') ||
        contentType.includes('application/octet-stream')
      )
    ) || (
      contentDisposition && contentDisposition.includes('attachment')
    );

    // Solo cachear si NO es descargable
    if (
      !isDownloadable &&
      (url.protocol === 'http:' || url.protocol === 'https:') &&
      url.origin === self.location.origin &&
      response &&
      (response.status === 200 || response.type === 'opaque')
    ) {
      await cache.put(request, response.clone());
    }

    return response;
  } catch (err) {
    if (cached) {
      return cached;
    }
    return Response.error();
  }
}

// =============== GESTIÓN DE MENSAJES =================
// Para forzar la descarga desde el frontend
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});