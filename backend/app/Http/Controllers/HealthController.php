<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    /**
     * Memeriksa kesehatan sistem (basis data dan cache).
     * Mengembalikan HTTP 200 jika semua sehat, atau HTTP 503 jika ada komponen bermasalah.
     */
    public function check(Request $request): JsonResponse
    {
        $checks = [];
        $failedComponents = [];

        // 1. Pemeriksaan Basis Data
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Koneksi basis data berhasil terhubung',
            ];
        } catch (\Throwable $e) {
            $failedComponents[] = 'database';
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Gagal terhubung ke basis data: ' . $e->getMessage(),
            ];
        }

        // 2. Pemeriksaan Cache
        try {
            $probeKey = 'health_probe_' . bin2hex(random_bytes(4));
            $probeValue = 'health_ok_' . microtime(true);
            Cache::put($probeKey, $probeValue, 10);
            $retrieved = Cache::get($probeKey);
            Cache::forget($probeKey);

            if ($retrieved === $probeValue) {
                $checks['cache'] = [
                    'status' => 'healthy',
                    'message' => 'Operasi baca/tulis cache berfungsi normal',
                ];
            } else {
                $failedComponents[] = 'cache';
                $checks['cache'] = [
                    'status' => 'unhealthy',
                    'message' => 'Nilai cache yang dibaca tidak sesuai dengan nilai yang disimpan',
                ];
            }
        } catch (\Throwable $e) {
            $failedComponents[] = 'cache';
            $checks['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Gagal mengakses penyimpanan cache: ' . $e->getMessage(),
            ];
        }

        $isHealthy = empty($failedComponents);
        $statusCode = $isHealthy ? 200 : 503;

        $responsePayload = [
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'failed_components' => $failedComponents,
            'checks' => $checks,
        ];

        if (! $isHealthy) {
            Log::error('Pemeriksaan kesehatan sistem gagal (unhealthy)', [
                'who' => [
                    'type' => 'system_healthcheck',
                    'client_ip' => $request->ip(),
                ],
                'action' => $request->method() . ' ' . $request->getRequestUri(),
                'reason' => 'Komponen bermasalah: ' . implode(', ', $failedComponents),
                'failed_components' => $failedComponents,
            ]);
        }

        return response()->json($responsePayload, $statusCode);
    }
}
