<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CheckActivation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get Machine ID from header
            $machineId = $request->header('X-Machine-ID');

            // Ensure machineId is a string for logging/usage
            if (is_array($machineId)) {
                $machineId = $machineId[0] ?? null;
            }

            // If no Machine ID header, let the request through
            // Frontend router guard will handle redirect to activation page
            if (!$machineId) {
                return $next($request);
            }

            // Check DB - if record exists, it's activated
            $activation = DB::table('activations')
                ->where('machine_id', strtoupper($machineId))
                ->first();

            if (!$activation) {
                return response()->json([
                    'error' => 'LICENSE_NOT_FOUND',
                    'message' => 'License not found for this machine',
                    'machine_id' => strtoupper($machineId)
                ], 403);
            }

            // Check expiry
            if ($activation->expired_at && $activation->expired_at < now()->toDateString()) {
                return response()->json([
                    'error' => 'LICENSE_EXPIRED',
                    'message' => 'License expired on ' . $activation->expired_at,
                    'expired_at' => $activation->expired_at
                ], 403);
            }

            return $next($request);

        } catch (\Throwable $e) {
            // On error, let request pass - we don't want to block users due to DB issues
            \Illuminate\Support\Facades\Log::error('CheckActivation middleware error: ' . $e->getMessage());
            return $next($request);
        }
    }
}
