const CACHE_NAME = 'salary-manager-v2';
const urlsToCache = [
  '/manifest.json',
  '/favicon.ico',
  '/logo192.png',
  '/logo512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        // Cache files individually and catch failures to prevent sw install failure if a single resource fails
        const cachePromises = urlsToCache.map(url => {
          return cache.add(url).catch(error => {
            console.warn(`[Service Worker] Failed to pre-cache: ${url}`, error);
          });
        });
        return Promise.all(cachePromises);
      })
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  // Only handle GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip navigation requests (page loads) to let the browser handle redirects natively and avoid Safari errors
  if (event.request.mode === 'navigate') {
    return;
  }

  // Skip non-HTTP requests (e.g. chrome-extension, data URIs)
  const url = new URL(event.request.url);
  if (!url.protocol.startsWith('http')) {
    return;
  }

  // Skip dynamic or API routes
  if (
    url.pathname.startsWith('/api/') || 
    url.pathname.startsWith('/employee/employee_manager/fetch') ||
    url.pathname.includes('/livewire/')
  ) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }

        return fetch(event.request).then(response => {
          // If the response is not valid, just return it
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // Strip Safari's internal redirect flags if redirected
          let responseToUse = response;
          if (response.redirected) {
            responseToUse = new Response(response.body, {
              status: response.status,
              statusText: response.statusText,
              headers: response.headers
            });
          }

          // Cache CSS, JS, Fonts, and Images dynamically
          const fileExtension = url.pathname.split('.').pop().toLowerCase();
          const cacheableExtensions = ['js', 'css', 'woff2', 'woff', 'ttf', 'png', 'jpg', 'jpeg', 'svg', 'ico'];
          if (cacheableExtensions.includes(fileExtension) || url.pathname.includes('/build/assets/')) {
            const responseToCache = responseToUse.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseToCache);
            });
          }

          return responseToUse;
        }).catch(err => {
          // Network failed, just propagate the failure or handle cleanly
          console.warn('[Service Worker] Fetch failed:', err);
        });
      })
  );
});
