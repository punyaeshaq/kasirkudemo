<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAll();

        // Check if store_logo file exists before adding URL
        if (isset($settings['store_logo']) && $settings['store_logo']) {
            if (Storage::disk('public')->exists($settings['store_logo'])) {
                $settings['store_logo_url'] = url('storage-files/' . $settings['store_logo']);
            } else {
                // File doesn't exist, remove the setting
                unset($settings['store_logo']);
            }
        }

        // Check if qris_image file exists before adding URL
        if (isset($settings['qris_image']) && $settings['qris_image']) {
            if (Storage::disk('public')->exists($settings['qris_image'])) {
                $settings['qris_image_url'] = url('storage-files/' . $settings['qris_image']);
            } else {
                // File doesn't exist, remove the setting
                unset($settings['qris_image']);
            }
        }
        return response()->json($settings)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function update(Request $request)
    {
        // Exclude file and method spoofing field
        $settings = $request->except(['store_logo', 'qris_image', '_method']);

        // Handle Store Logo Upload
        if ($request->hasFile('store_logo')) {
            $path = $request->file('store_logo')->store('uploads', 'public');
            Setting::set('store_logo', $path);
        }

        // Handle QRIS Image Upload
        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->store('uploads', 'public');
            Setting::set('qris_image', $path);
        } elseif ($request->has('qris_image') && $request->qris_image === '') {
            // Handle QRIS removal
            Setting::set('qris_image', '');
        }

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        $allSettings = Setting::getAll();

        // Add logo URL if logo file exists
        if (isset($allSettings['store_logo']) && $allSettings['store_logo']) {
            if (Storage::disk('public')->exists($allSettings['store_logo'])) {
                $allSettings['store_logo_url'] = url('storage-files/' . $allSettings['store_logo']);
            } else {
                unset($allSettings['store_logo']);
            }
        }

        // Add QRIS URL if QRIS file exists
        if (isset($allSettings['qris_image']) && $allSettings['qris_image']) {
            if (Storage::disk('public')->exists($allSettings['qris_image'])) {
                $allSettings['qris_image_url'] = url('storage-files/' . $allSettings['qris_image']);
            } else {
                unset($allSettings['qris_image']);
            }
        }

        return response()->json([
            'message' => 'Pengaturan disimpan',
            'data' => $allSettings
        ]);
    }
}
