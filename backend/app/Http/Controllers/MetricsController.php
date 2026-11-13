<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    /**
     * Mengekspos metrik aplikasi Laravel dalam format Prometheus text exposition (0.0.4).
     */
    public function index(): Response
    {
        $dbHealthy = 0;
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $dbHealthy = 1;
        } catch (\Throwable) {
            $dbHealthy = 0;
        }

        $cacheHealthy = 0;
        try {
            $probeKey = 'metrics_probe_' . bin2hex(random_bytes(4));
            $probeVal = 'ok_' . microtime(true);
            Cache::put($probeKey, $probeVal, 10);
            $retrieved = Cache::get($probeKey);
            Cache::forget($probeKey);
            $cacheHealthy = ($retrieved === $probeVal) ? 1 : 0;
        } catch (\Throwable) {
            $cacheHealthy = 0;
        }

        $appUp = ($dbHealthy === 1 && $cacheHealthy === 1) ? 1 : 0;

        $counters = [];
        $metricsFile = storage_path('app/prometheus_metrics.json');
        if (is_file($metricsFile)) {
            $fp = @fopen($metricsFile, 'r');
            if ($fp !== false) {
                if (flock($fp, LOCK_SH)) {
                    $raw = stream_get_contents($fp);
                    flock($fp, LOCK_UN);
                    if (is_string($raw) && $raw !== '') {
                        $decoded = json_decode($raw, true);
                        if (is_array($decoded)) {
                            $counters = $decoded;
                        }
                    }
                }
                fclose($fp);
            }
        }

        if (empty($counters)) {
            $counters['GET|/health|200'] = 0;
        }

        ksort($counters);

        $lines = [
            '# HELP app_up Status kesehatan keseluruhan aplikasi Akuntansi Pemuda (1 = sehat, 0 = bermasalah)',
            '# TYPE app_up gauge',
            'app_up ' . $appUp,
            '# HELP app_health_status Status kesehatan masing-masing komponen aplikasi (1 = healthy, 0 = unhealthy)',
            '# TYPE app_health_status gauge',
            'app_health_status{component="database"} ' . $dbHealthy,
            'app_health_status{component="cache"} ' . $cacheHealthy,
            '# HELP app_http_requests_total Jumlah kumulatif permintaan HTTP yang diproses backend',
            '# TYPE app_http_requests_total counter',
        ];

        foreach ($counters as $key => $value) {
            $parts = explode('|', (string) $key, 3);
            if (count($parts) !== 3) {
                continue;
            }
            [$method, $route, $status] = $parts;
            $safeMethod = $this->escapeLabelValue($method);
            $safeRoute = $this->escapeLabelValue($route);
            $safeStatus = $this->escapeLabelValue($status);
            $lines[] = sprintf(
                'app_http_requests_total{method="%s",route="%s",status="%s"} %d',
                $safeMethod,
                $safeRoute,
                $safeStatus,
                (int) $value
            );
        }

        $body = implode("\n", $lines) . "\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }

    private function escapeLabelValue(string $value): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\n'], $value);
    }
}
