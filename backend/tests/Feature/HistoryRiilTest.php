<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\RiilHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryRiilTest extends TestCase
{
    use RefreshDatabase;

    private function getBendaharaToken(): string
    {
        $bendahara = User::factory()->create([
            'role' => 'bendahara',
            'status' => 'Aktif',
        ]);

        return $bendahara->createToken('auth_token')->plainTextToken;
    }

    public function test_bendahara_can_list_history_riil(): void
    {
        $token = $this->getBendaharaToken();

        $akun = Akun::create([
            'nama_akun' => 'Kas Utama Pemuda',
            'kas' => 'kas pemuda',
            'jumlah' => 0,
        ]);

        RiilHistory::create([
            'akun_id' => $akun->id,
            'date' => '2026-01-15',
            'riil' => 500000,
            'ket' => 'Setoran awal',
            'verified' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/history-riil?page=1&per_page=10&tanggal_mulai=2026-01-01&tanggal_selesai=2026-01-31');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data history riil berhasil diambil')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.riil', '500000.00');
    }

    public function test_bendahara_can_verify_history_riil_and_update_akun_balance(): void
    {
        $token = $this->getBendaharaToken();

        $akun = Akun::create([
            'nama_akun' => 'Kas Utama Pemuda',
            'kas' => 'kas pemuda',
            'jumlah' => 0,
        ]);

        $olderHistory = RiilHistory::create([
            'akun_id' => $akun->id,
            'date' => '2026-01-10',
            'riil' => 300000,
            'ket' => 'Riil lama',
            'verified' => false,
        ]);

        $newerHistory = RiilHistory::create([
            'akun_id' => $akun->id,
            'date' => '2026-01-20',
            'riil' => 750000,
            'ket' => 'Riil baru',
            'verified' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/history-riil/{$newerHistory->id}/verify");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'History riil berhasil diverifikasi')
            ->assertJsonPath('data.verified', true);

        $this->assertTrue((bool) $olderHistory->fresh()->verified);
        $this->assertTrue((bool) $newerHistory->fresh()->verified);
        $this->assertEquals(750000, $akun->fresh()->jumlah);
        $this->assertEquals($newerHistory->id, $akun->fresh()->riil_terakhir);
    }
}
