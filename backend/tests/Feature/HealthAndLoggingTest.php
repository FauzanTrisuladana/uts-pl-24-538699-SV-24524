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
        // Simulasikan kegagalan basis data dengan memutuskan koneksi default ke basis data yang tidak valid
        config(['database.connections.sqlite.database' => '/non_existent_directory/invalid.sqlite']);
        DB::purge('sqlite');

        $response = $this->getJson('/health');

        $response->assertStatus(503)
            ->assertJson([
                'status' => 'unhealthy',
            ]);

        $this->assertContains('database', $response->json('failed_components'));
        $this->assertEquals('unhealthy', $response->json('checks.database.status'));
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
}
