<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SetupController extends Controller
{
    protected $backupPath = 'backups';

    public function __construct()
    {
        // Ensure backup directory exists
        if (!Storage::exists($this->backupPath)) {
            Storage::makeDirectory($this->backupPath);
        }
    }

    /**
     * Check if setup is completed
     * Setup is considered complete if store_name exists in settings
     */
    public function status()
    {
        try {
            $storeName = DB::table('settings')
                ->where('key', 'store_name')
                ->first();

            $isComplete = $storeName && !empty($storeName->value) && $storeName->value !== 'Toko KasirKu';

            return response()->json([
                'setup_completed' => $isComplete,
                'store_name' => $storeName ? $storeName->value : null
            ]);
        } catch (\Exception $e) {
            // If settings table doesn't exist, setup is not complete
            return response()->json([
                'setup_completed' => false,
                'store_name' => null
            ]);
        }
    }

    /**
     * Create new store - save initial store settings
     */
    public function createStore(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string',
            'store_phone' => 'nullable|string|max:50',
            'store_logo' => 'nullable|file|image|max:2048' // Max 2MB
        ]);

        try {
            // Save settings
            $settings = [
                'store_name' => $request->store_name,
                'store_address' => $request->store_address ?? '',
                'store_phone' => $request->store_phone ?? '',
                'tax_rate' => '11',
                'receipt_footer_text' => 'TERIMA KASIH',
                'receipt_note' => 'Barang yang sudah dibeli tidak dapat ditukar/dikembalikan',
                'receipt_website' => '',
                'receipt_printer_width' => '58mm',
                'setup_completed' => 'true'
            ];

            foreach ($settings as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }

            // Handle logo upload
            if ($request->hasFile('store_logo')) {
                $logo = $request->file('store_logo');
                $logoName = 'store_logo_' . time() . '.' . $logo->getClientOriginalExtension();

                // Save to storage/app/public/logos
                $logo->storeAs('public/logos', $logoName);

                // Save logo path to settings
                DB::table('settings')->updateOrInsert(
                    ['key' => 'store_logo'],
                    ['value' => 'storage/logos/' . $logoName, 'updated_at' => now()]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Toko berhasil dibuat! Selamat menggunakan KasirKu.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat toko: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore database from uploaded file (simplified from BackupController)
     */
    public function restoreDatabase(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400' // Max 100MB
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        // Validate file extension
        if (!in_array(strtolower($extension), ['sql', 'txt'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format file tidak valid. Gunakan file .sql'
            ], 422);
        }

        try {
            // Save uploaded file
            $filename = 'setup_restore_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = storage_path('app/' . $this->backupPath . '/' . $filename);
            $file->move(storage_path('app/' . $this->backupPath), $filename);

            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            // Try using mysql command
            $command = sprintf(
                'mysql --user=%s --password=%s --host=%s --port=%s %s < %s 2>&1',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                // Fallback: Manual PHP restore
                $this->restoreManual($filepath);
            }

            // Mark setup as complete after restore
            DB::table('settings')->updateOrInsert(
                ['key' => 'setup_completed'],
                ['value' => 'true', 'updated_at' => now()]
            );

            return response()->json([
                'success' => true,
                'message' => 'Database berhasil dipulihkan! Selamat menggunakan KasirKu.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual PHP restore (fallback)
     */
    protected function restoreManual($filepath)
    {
        $sql = file_get_contents($filepath);

        // Remove comments and split by semicolon
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($statements as $statement) {
            if (!empty($statement) && $statement !== 'SET FOREIGN_KEY_CHECKS=0' && $statement !== 'SET FOREIGN_KEY_CHECKS=1') {
                DB::unprepared($statement);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Mark setup as complete (called after any setup method)
     */
    public function markComplete()
    {
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => 'setup_completed'],
                ['value' => 'true', 'updated_at' => now()]
            );

            return response()->json([
                'success' => true,
                'message' => 'Setup selesai!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai setup selesai: ' . $e->getMessage()
            ], 500);
        }
    }
}
