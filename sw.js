// sw.js
const CACHE_NAME = "VincentApp-v2";
const ASSETS = [
  // Core PHP files
  "/",
  "/index.php",
  "/question.php",
  "/login.php",
  "/register.php",

  // Critical CSS/JS
  "/CSS/global.css",
  "/CSS/header.css",
  "/CSS/questions.css",
  "/JS/index.js",
  
  // Static assets
  "/images/svcc.jpg",
  "/images/userAvatar.jpg"
];

// Install: Cache essential files
self.addEventListener("install", (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(ASSETS))
  );
});

// Fetch: Serve cached files when offline
self.addEventListener("fetch", (e) => {
  e.respondWith(
    caches.match(e.request)
      .then(cached => cached || fetch(e.request))
  );
});