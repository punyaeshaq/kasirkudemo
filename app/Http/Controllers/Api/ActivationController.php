<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivationController extends Controller
{
    private string $secretSalt;

    public function __construct()
    {
        $this->secretSalt = env('ACTIVATION_SALT', 'KasirKu-Default-Key');
    }

    /**
     * Check if current user is superadmin
     */
    private function ensureSuperAdmin()
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }
    }

    /**
     * List semua aktivasi (Super Admin only)
     */
    public function index()
    {
        $this->ensureSuperAdmin();

        $activations = DB::table('activations')
            ->orderBy('activated_at', 'desc')
            ->get()
            ->map(function ($item) {
                $now = now()->format('Y-m-d');
                $item->is_expired = $now > $item->expired_at;
                $item->days_remaining = max(0, now()->diffInDays($item->expired_at, false));
                return $item;
            });

        return response()->json([
            'data' => $activations,
            'total' => $activations->count()
        ]);
    }

    /**
     * Revoke/hapus aktivasi (Super Admin only)
     */
    public function revoke(Request $request)
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'machine_id' => 'required|string'
        ]);

        $machineId = strtoupper($request->input('machine_id'));

        $deleted = DB::table('activations')
            ->where('machine_id', $machineId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => "Aktivasi untuk Machine ID {$machineId} berhasil dihapus"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Machine ID tidak ditemukan'
        ], 404);
    }

    /**
     * Cek status aktivasi berdasarkan Machine ID
     */
    public function status(Request $request)
    {
        $machineId = $request->input('machine_id');

        if (!$machineId) {
            return response()->json([
                'activated' => false,
                'message' => 'Machine ID diperlukan'
            ]);
        }

        $activation = DB::table('activations')
            ->where('machine_id', strtoupper($machineId))
            ->first();

        if (!$activation) {
            return response()->json([
                'activated' => false,
                'machine_id' => strtoupper($machineId),
                'message' => 'Aplikasi belum diaktivasi'
            ]);
        }

        // Cek expired
        $expiredDate = $activation->expired_at;
        $now = now()->format('Y-m-d');

        if ($now > $expiredDate) {
            return response()->json([
                'activated' => false,
                'expired' => true,
                'expired_at' => $expiredDate,
                'machine_id' => strtoupper($machineId),
                'message' => 'Lisensi sudah expired pada ' . $expiredDate
            ]);
        }

        return response()->json([
            'activated' => true,
            'expired_at' => $expiredDate,
            'activated_at' => $activation->activated_at,
            'machine_id' => strtoupper($machineId),
            'message' => 'Aplikasi teraktivasi'
        ]);
    }

    /**
     * Aktivasi aplikasi dengan kode aktivasi
     */
    public function activate(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|string',
            'activation_code' => 'required|string'
        ]);

        $machineId = strtoupper($request->input('machine_id'));
        $activationCode = strtoupper(str_replace(' ', '', $request->input('activation_code')));

        // Validasi format kode aktivasi
        $parts = explode('-', $activationCode);

        if (count($parts) !== 5) {
            return response()->json([
                'success' => false,
                'message' => 'Format kode aktivasi tidak valid'
            ], 422);
        }

        // Extract date code dari bagian terakhir (YYMMDD)
        $dateCode = $parts[4];
        if (!preg_match('/^\d{6}$/', $dateCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal dalam kode tidak valid'
            ], 422);
        }

        // Reconstruct expired date
        $year = '20' . substr($dateCode, 0, 2);
        $month = substr($dateCode, 2, 2);
        $day = substr($dateCode, 4, 2);
        $expiredDate = "{$year}-{$month}-{$day}";

        // Validate the date
        if (!checkdate((int) $month, (int) $day, (int) $year)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal expired dalam kode tidak valid'
            ], 422);
        }

        // Regenerate code untuk perbandingan
        $expectedCode = $this->generateActivationCode($machineId, $expiredDate);

        if ($activationCode !== $expectedCode) {
            return response()->json([
                'success' => false,
                'message' => 'Kode aktivasi tidak valid untuk Machine ID ini'
            ], 422);
        }

        // Cek expired
        $now = now()->format('Y-m-d');
        if ($now > $expiredDate) {
            return response()->json([
                'success' => false,
                'message' => 'Kode aktivasi sudah expired'
            ], 422);
        }

        // Simpan atau update aktivasi
        DB::table('activations')->updateOrInsert(
            ['machine_id' => $machineId],
            [
                'activation_code' => $activationCode,
                'expired_at' => $expiredDate,
                'activated_at' => now(),
                'updated_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'expired_at' => $expiredDate,
            'message' => 'Aktivasi berhasil! Lisensi berlaku hingga ' . $expiredDate
        ]);
    }

    /**
     * Generate activation code (internal use)
     * HARUS SAMA dengan logic di activation-generator.js
     */
    private function generateActivationCode(string $machineId, string $expiredDate): string
    {
        // Gabungkan data untuk signing
        $dataToSign = strtoupper($machineId) . '|' . $expiredDate . '|' . $this->secretSalt;

        // Generate HMAC-SHA256
        $signature = hash_hmac('sha256', $dataToSign, $this->secretSalt);

        // Ambil 16 karakter pertama dan format dengan dash
        $shortCode = strtoupper(substr($signature, 0, 16));
        $formattedCode = substr($shortCode, 0, 4) . '-' .
            substr($shortCode, 4, 4) . '-' .
            substr($shortCode, 8, 4) . '-' .
            substr($shortCode, 12, 4);

        // Encode expired date ke format singkat (YYMMDD)
        $dateCode = substr(str_replace('-', '', $expiredDate), 2); // YYMMDD

        // Final activation code
        return $formattedCode . '-' . $dateCode;
    }
}
