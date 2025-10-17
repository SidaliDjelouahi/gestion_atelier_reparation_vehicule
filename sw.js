// ✅ sw.js — fonctionne à la fois sur localhost (index.php) et Hostinger (default.php)
const CACHE_NAME = 'atelier-cache-v4';

// 🔍 Détection automatique du domaine et du chemin de base
let BASE_URL = '';
let HOME_PAGE = '/index.php';

const hostname = self.location.hostname;

// En local (XAMPP)
if (hostname === 'localhost') {
  BASE_URL = '/gestion_atelier_reparation_vehicule';
  HOME_PAGE = '/index.php';
}
// En ligne (Hostinger : gmi.unisoft-dz.com)
else if (hostname === 'gmi.unisoft-dz.com') {
  BASE_URL = ''; // racine du sous-domaine
  HOME_PAGE = '/default.php';
}

// 🔖 Fichiers à mettre en cache (base + icônes + manifest)
const urlsToCache = [
  `${BASE_URL}/`,
  `${BASE_URL}${HOME_PAGE}`,
  `${BASE_URL}/manifest.json`,
  `${BASE_URL}/icon-192.png`,
  `${BASE_URL}/icon-512.png`
];

// 🧩 Installation — mise en cache initiale
self.addEventListener('install', event => {
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_NAME);
      for (const url of urlsToCache) {
        try {
          const response = await fetch(url);
          if (response.ok) {
            await cache.put(url, response.clone());
            console.log('✅ Mis en cache :', url);
          } else {
            console.warn('⚠️ Introuvable :', url, `(status ${response.status})`);
          }
        } catch (err) {
          console.error('❌ Erreur de mise en cache :', url, err);
        }
      }
    })()
  );
  self.skipWaiting();
});

// 🧹 Activation — suppression des anciens caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(k => k !== CACHE_NAME)
          .map(k => {
            console.log('🗑️ Suppression du cache obsolète :', k);
            return caches.delete(k);
          })
      )
    )
  );
  self.clients.claim();
});

// 🌐 Interception des requêtes — stratégie cache-first
self.addEventListener('fetch', event => {
  const req = event.request;

  // Ignorer les requêtes externes (autres domaines)
  if (new URL(req.url).origin !== location.origin) return;

  event.respondWith(
    caches.match(req).then(cached => {
      if (cached) return cached; // retourne directement du cache

      // Sinon, va sur le réseau et met à jour le cache
      return fetch(req)
        .then(response => {
          if (req.method === 'GET' && response && response.status === 200) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(req, clone));
          }
          return response;
        })
        .catch(() => {
          // Si offline → retourne la page d’accueil
          if (req.destination === 'document') {
            return caches.match(`${BASE_URL}${HOME_PAGE}`);
          }
        });
    })
  );
});
