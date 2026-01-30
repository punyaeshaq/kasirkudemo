<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
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
     * Display list of available backups
     */
    public function index()
    {
        $files = Storage::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes(Storage::size($file)),
                    'size_bytes' => Storage::size($file),
                    'created_at' => date('Y-m-d H:i:s', Storage::lastModified($file)),
                ];
            }
        }

        // Sort by newest first
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return response()->json([
            'success' => true,
            'data' => $backups
        ]);
    }

    /**
     * Create a new database backup
     */
    public function create()
    {
        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = storage_path('app/' . $this->backupPath . '/' . $filename);

            // Build mysqldump command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s 2>&1',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Execute backup
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($filepath)) {
                // Fallback: Manual PHP backup
                return $this->createManualBackup($filename);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dibuat',
                'filename' => $filename,
                'size' => $this->formatBytes(filesize($filepath))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual PHP backup (fallback if mysqldump not available)
     */
    protected function createManualBackup($filename)
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $database = config('database.connections.mysql.database');
            $tableKey = 'Tables_in_' . $database;

            $sql = "-- KasirKu Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: {$database}\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Get create table statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "-- Table structure for `{$tableName}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

                // Get table data
                $rows = DB::table($tableName)->get();

                if ($rows->count() > 0) {
                    $sql .= "-- Data for `{$tableName}`\n";

                    foreach ($rows as $row) {
                        $values = array_map(function ($value) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return "'" . addslashes($value) . "'";
                        }, (array) $row);

                        $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Save to file
            Storage::put($this->backupPath . '/' . $filename, $sql);

            $filepath = storage_path('app/' . $this->backupPath . '/' . $filename);

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dibuat',
                'filename' => $filename,
                'size' => $this->formatBytes(filesize($filepath))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore database from backup file
     */
    public function restore(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filename = $request->filename;
        $filepath = storage_path('app/' . $this->backupPath . '/' . $filename);

        if (!file_exists($filepath)) {
            return response()->json([
                'success' => false,
                'message' => 'File backup tidak ditemukan'
            ], 404);
        }

        try {
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
                return $this->restoreManual($filepath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Database berhasil dipulihkan dari backup'
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
        try {
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

            return response()->json([
                'success' => true,
                'message' => 'Database berhasil dipulihkan dari backup'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore database from uploaded file
     */
    public function restoreFromUpload(Request $request)
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
            // Save uploaded file temporarily
            $filename = 'upload_' . date('Y-m-d_H-i-s') . '.sql';
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

            // Optionally delete the uploaded file after restore
            // Storage::delete($this->backupPath . '/' . $filename);

            return response()->json([
                'success' => true,
                'message' => 'Database berhasil dipulihkan dari file yang diupload'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download($filename)
    {
        $filepath = storage_path('app/' . $this->backupPath . '/' . $filename);

        if (!file_exists($filepath)) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 404);
        }

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/sql'
        ]);
    }

    /**
     * Delete backup file
     */
    public function destroy($filename)
    {
        $filepath = $this->backupPath . '/' . $filename;

        if (!Storage::exists($filepath)) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 404);
        }

        Storage::delete($filepath);

        return response()->json([
            'success' => true,
            'message' => 'Backup berhasil dihapus'
        ]);
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
