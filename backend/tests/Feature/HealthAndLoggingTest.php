<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class HealthAndLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_200_when_healthy(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
                'failed_components' => [],
                'checks' => [
                    'database' => [
                        'status' => 'healthy',
                    ],
                    'cache' => [
                        'status' => 'healthy',
                    ],
                ],
            ]);

        // Uji juga via endpoint /api/health
        $apiResponse = $this->getJson('/api/health');
        $apiResponse->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
            ]);
    }

    public function test_health_endpoint_returns_503_when_database_fails(): void
    {
        $defaultConn = config('database.default');
        $origPort = config("database.connections.{$defaultConn}.port");
        $origDb = config("database.connections.{$defaultConn}.database");

        try {
            DB::disconnect();
            config(["database.connections.{$defaultConn}.port" => 9999]);
            config(["database.connections.{$defaultConn}.database" => '/non_existent_directory/invalid.sqlite']);
            DB::purge($defaultConn);

            $response = $this->getJson('/health');

            $response->assertStatus(503)
                ->assertJson([
                    'status' => 'unhealthy',
                ]);

            $this->assertContains('database', $response->json('failed_components'));
            $this->assertEquals('unhealthy', $response->json('checks.database.status'));
        } finally {
            DB::disconnect();
            config(["database.connections.{$defaultConn}.port" => $origPort]);
            config(["database.connections.{$defaultConn}.database" => $origDb]);
            DB::purge($defaultConn);
            DB::reconnect($defaultConn);
        }
    }

    public function test_structured_log_recorded_on_validation_failure(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Validasi data gagal'
                    && isset($context['who'], $context['action'], $context['reason'], $context['invalid_fields'])
                    && in_array('nama', $context['invalid_fields'], true);
            });

        $user = User::factory()->create([
            'role' => 'bendahara',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/penanggung-jawab', [
            'nama' => '', // Gagal validasi: nama wajib diisi
        ]);

        $response->assertStatus(422);
    }

    public function test_structured_log_recorded_on_data_creation(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Berhasil menyimpan data penanggung jawab'
                    && isset($context['who']['user_id'], $context['action'], $context['reason'])
                    && $context['action'] === 'POST /api/penanggung-jawab';
            });

        $user = User::factory()->create([
            'role' => 'bendahara',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/penanggung-jawab', [
            'nama' => 'Penanggung Jawab Test Log',
        ]);

        $response->assertStatus(201);
    }

    public function test_metrics_endpoint_returns_prometheus_format(): void
    {
        $this->getJson('/health')->assertStatus(200);

        $response = $this->get('/metrics');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        $content = (string) $response->getContent();
        $this->assertStringContainsString('# TYPE app_up gauge', $content);
        $this->assertStringContainsString('app_up 1', $content);
        $this->assertStringContainsString('app_health_status{component="database"} 1', $content);
        $this->assertStringContainsString('app_health_status{component="cache"} 1', $content);
        $this->assertStringContainsString('app_http_requests_total{method="GET",route="/health",status="200"}', $content);
    }
}
