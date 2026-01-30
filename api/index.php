<?php

/*
|--------------------------------------------------------------------------
| VERCEL PATCH: Inject Environment Variables
|--------------------------------------------------------------------------
|
| Vercel passes environment variables through $_SERVER. Laravel's env()
| helper relies on getenv() and $_ENV. This patch syncs them.
|
*/

// List of critical environment variables that must be injected
$envVars = [
    'APP_NAME',
    'APP_ENV',
    'APP_KEY',
    'APP_DEBUG',
    'APP_URL',
    'LOG_CHANNEL',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'CACHE_DRIVER',
    'SESSION_DRIVER',
    'QUEUE_CONNECTION',
    'FILESYSTEM_DISK',
];

// VERCEL PATCH: URI correction for API requests
// Vercel strips the /api prefix. We detect API routes by their path patterns
// and restore the prefix so Laravel's RouteServiceProvider can match them.
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';

// List of known API route prefixes (from routes/api.php)
$apiPrefixes = [
    '/auth/',
    '/activation/',
    '/setup/',
    '/dashboard',
    '/categories',
    '/products',
    '/transactions',
    '/reports/',
    '/settings',
    '/customers',
    '/debts',
    '/discounts',
    '/users',
    '/backup/',
    '/activations',
    '/print-receipt/',
];

// Check if path matches any API route pattern
$isApiRoute = false;
foreach ($apiPrefixes as $prefix) {
    if (str_starts_with($path, $prefix)) {
        $isApiRoute = true;
        break;
    }
}

// If it's an API route and doesn't already have /api prefix, add it
if ($isApiRoute && !str_starts_with($path, '/api')) {
    $_SERVER['REQUEST_URI'] = '/api' . $uri;
}

foreach ($envVars as $var) {
    if (isset($_SERVER[$var]) && !empty($_SERVER[$var])) {
        putenv("$var=" . $_SERVER[$var]);
        $_ENV[$var] = $_SERVER[$var];
    }
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

require __DIR__ . '/../public/index.php';
