<?php

// VERCEL PATCH: Ensure environment variables are loaded
if (isset($_SERVER['APP_KEY'])) {
    putenv('APP_KEY=' . $_SERVER['APP_KEY']);
    $_ENV['APP_KEY'] = $_SERVER['APP_KEY'];
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

require __DIR__ . '/../public/index.php';
