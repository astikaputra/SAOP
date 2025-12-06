<?php
// Tambahkan di $routeMiddleware array
protected $routeMiddleware = [
    // ... middleware lainnya
    'role' => \App\Http\Middleware\CheckRole::class,
];