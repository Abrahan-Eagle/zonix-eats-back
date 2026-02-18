const CACHE_NAME = 'corral-x-v1.0.0';
const urlsToCache = [
  '/',
  '/corralX.html',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap',
  '/assets/front/images/Favicon/web-app-manifest-192x192.png',
  '/assets/front/images/images/phone-mockup.jpg'
];

// Instalación del Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache abierto');
        return cache.addAll(urlsToCache);
      })
  );
});

// Activación del Service Worker
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Eliminando cache antiguo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Interceptar peticiones
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Devolver desde cache si existe
        if (response) {
          return response;
        }
        
        // Si no está en cache, hacer la petición a la red
        return fetch(event.request).then(response => {
          // Verificar que la respuesta sea válida
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }
          
          // Clonar la respuesta para cachearla
          const responseToCache = response.clone();
          
          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            });
          
          return response;
        });
      })
      .catch(() => {
        // Fallback para páginas offline
        if (event.request.mode === 'navigate') {
          return caches.match('/corralX.html');
        }
      })
  );
});

// Mensajes del Service Worker
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// Sincronización en background
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

function doBackgroundSync() {
  // Aquí puedes implementar sincronización de datos
  console.log('Sincronización en background ejecutada');
}

// Push notifications
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : '¡Nueva notificación de Corral X!',
    icon: '/assets/Favicon/web-app-manifest-192x192.png',
    badge: '/assets/Favicon/web-app-manifest-192x192.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Ver más',
        icon: '/assets/Favicon/web-app-manifest-192x192.png'
      },
      {
        action: 'close',
        title: 'Cerrar',
        icon: '/assets/Favicon/web-app-manifest-192x192.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('Corral X', options)
  );
});

// Click en notificaciones
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});
