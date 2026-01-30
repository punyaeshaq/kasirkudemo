<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


// Route to serve storage files (for Windows development without symbolic link)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    $mimeType = mime_content_type($fullPath);
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');

// Explicit route for portable file serving (bypassing symlink checks)
Route::get('/storage-files/{path}', [App\Http\Controllers\FileController::class, 'show'])->where('path', '.*');


// SPA catch-all route - exclude api, sanctum, and storage paths
// Static files (.png, .jpg, .css, .js, etc.) are served directly by the web server
// and should not reach this route
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api|sanctum|storage).*$')->name('login');
