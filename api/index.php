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

// Note: API prefix handling is now done in RouteServiceProvider
// In production (Vercel), routes are registered without /api prefix

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
