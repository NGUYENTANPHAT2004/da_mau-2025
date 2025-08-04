<?php
class PWAController {
    public function manifest() {
        $manifest = [
            "name" => SITE_NAME,
            "short_name" => "FPolyShop",
            "description" => "Website bán hàng trực tuyến",
            "start_url" => BASE_URL,
            "display" => "standalone",
            "background_color" => "#ffffff",
            "theme_color" => "#667eea",
            "icons" => [
                [
                    "src" => BASE_URL . "assets/icons/icon-192x192.png",
                    "sizes" => "192x192",
                    "type" => "image/png"
                ],
                [
                    "src" => BASE_URL . "assets/icons/icon-512x512.png",
                    "sizes" => "512x512",
                    "type" => "image/png"
                ]
            ]
        ];
        
        echo json_encode($manifest);
    }

    public function serviceWorker() {
        echo "
        const CACHE_NAME = 'fpolyshop-v1';
        const urlsToCache = [
            '/',
            '/assets/css/style.css',
            '/assets/js/script.js',
            'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
        ];

        self.addEventListener('install', function(event) {
            event.waitUntil(
                caches.open(CACHE_NAME)
                    .then(function(cache) {
                        return cache.addAll(urlsToCache);
                    })
            );
        });

        self.addEventListener('fetch', function(event) {
            event.respondWith(
                caches.match(event.request)
                    .then(function(response) {
                        if (response) {
                            return response;
                        }
                        return fetch(event.request);
                    }
                )
            );
        });
        ";
    }
}


?>