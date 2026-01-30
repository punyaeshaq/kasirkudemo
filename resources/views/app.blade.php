<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KasirKu - Point of Sale Modern</title>
    <meta name="description" content="Aplikasi kasir modern untuk toko ritel. Mudah digunakan, cepat, dan efisien.">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/kasirku/public/favicon.png">

    <!-- PWA -->
    <link rel="manifest" href="/kasirku/public/manifest.json">
    <meta name="theme-color" content="#3a4d8c">
    <link rel="apple-touch-icon" href="/kasirku/public/icons/icon-192x192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-dark-50 dark:bg-dark-900">
    <div id="app"></div>
</body>

</html>