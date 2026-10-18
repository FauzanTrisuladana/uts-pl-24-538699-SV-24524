<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_dashboard_data(): void
    {
        $user = User::factory()->create([
            'role' => 'biasa',
            'status' => 'Aktif',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $akun = Akun::create([
            'nama_akun' => 'Kas Utama Pemuda',
            'kas' => 'kas pemuda',
        ]);

        Transaksi::create([
            'deskripsi' => 'Iuran Bulanan',
            'date' => now()->format('Y-m-d'),
            'jenis_transaksi' => 'pemasukan',
            'akun_id' => $akun->id,
            'jumlah' => 250000,
            'penginput_id' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard?kas=kas pemuda');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data untuk dashboard berhasil diambil')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'saldo_awal',
                    'saldo_daily',
                    'saldo_per_akun',
                    'rekonsiliasi',
                ],
                'summary' => [
                    'pemasukan' => ['total', 'change'],
                    'pengeluaran' => ['total', 'change'],
                    'totalSaldo' => ['total'],
                ],
            ]);
    }

    public function test_dashboard_requires_valid_kas_parameter(): void
    {
        $user = User::factory()->create([
            'role' => 'biasa',
            'status' => 'Aktif',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard?kas=invalid_kas');

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors' => ['kas']]);
    }
}
