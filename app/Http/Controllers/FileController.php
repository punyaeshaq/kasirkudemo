<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function show($path)
    {
        // Add security check to prevent directory traversal
        if (strpos($path, '..') !== false) {
            abort(403);
        }

        // We assume files are in storage/app/public
        // The path passed might be "uploads/logo.png" or "products/image.jpg"
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = storage_path('app/public/' . $path);

        return response()->file($fullPath);
    }
}
