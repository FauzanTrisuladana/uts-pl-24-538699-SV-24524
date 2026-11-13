<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordMetricsMiddleware
{
    /**
     * Mencatat jumlah permintaan HTTP per method, route, dan status code
     * ke berkas metrik Prometheus dengan penguncian berkas (flock).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $path = '/' . ltrim($request->getPathInfo(), '/');
            if ($path === '/metrics' || $path === '/api/metrics') {
                return $response;
            }

            $routeUri = $request->route()?->uri();
            $normalizedRoute = $routeUri ? '/' . ltrim($routeUri, '/') : $path;
            $method = strtoupper($request->getMethod());
            $status = (string) $response->getStatusCode();

            $metricsFile = storage_path('app/prometheus_metrics.json');
            $dir = dirname($metricsFile);
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $fp = @fopen($metricsFile, 'c+');
            if ($fp !== false) {
                if (flock($fp, LOCK_EX)) {
                    $raw = stream_get_contents($fp);
                    $data = [];
                    if (is_string($raw) && $raw !== '') {
                        $decoded = json_decode($raw, true);
                        if (is_array($decoded)) {
                            $data = $decoded;
                        }
                    }

                    $key = $method . '|' . $normalizedRoute . '|' . $status;
                    $data[$key] = (int) ($data[$key] ?? 0) + 1;

                    ftruncate($fp, 0);
                    rewind($fp);
                    fwrite($fp, (string) json_encode($data, JSON_PRETTY_PRINT));
                    fflush($fp);
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            }
        } catch (\Throwable) {
            // Pencatatan metrik tidak boleh menggagalkan response aplikasi utama
        }

        return $response;
    }
}
