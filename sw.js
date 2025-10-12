// sw.js - Service Worker simple (cache-first)
const CACHE_NAME = 'atelier-cache-v1';
const urlsToCache = [
  '/',
  '/default.php',
  '/index.php',
  '/assets/css/style.css',
  '/manifest.json',
  '/icon-192.png',
  '/icon-512.png'
];

// Installation : mettre en cache les ressources de base
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
  self.skipWaiting();
});

// Activation : nettoyage des anciens caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch : stratégie cache-first, fallback réseau
self.addEventListener('fetch', event => {
  const req = event.request;
  // Ignore les requêtes cross-origin (optional)
  if (new URL(req.url).origin !== location.origin) {
    return;
  }

  event.respondWith(
    caches.match(req).then(cached => {
      if (cached) return cached;
      return fetch(req).then(response => {
        // mise en cache des réponses GET valides
        if (req.method === 'GET' && response && response.status === 200) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(req, clone));
        }
        return response;
      }).catch(() => {
        // fallback simple : si c'est une page html, retourner la page d'accueil en cache
        if (req.destination === 'document') {
          return caches.match('/default.php');
        }
      });
    })
  );
});
