<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\PenanggungJawab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransaksiTest extends TestCase
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

    public function test_bendahara_can_create_transaksi_without_bukti_and_update_riil_history(): void
    {
        $token = $this->getBendaharaToken();

        $akun = Akun::create([
            'nama_akun' => 'Kas Utama Pemuda',
            'kas' => 'kas pemuda',
        ]);

        $pj = PenanggungJawab::create([
            'nama' => 'Ahmad Fauzi',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/transaksi', [
                'deskripsi' => 'Iuran Bulanan Pemuda',
                'date' => '2026-03-05',
                'jenis_transaksi' => 'pemasukan',
                'akun_id' => $akun->id,
                'penanggung_jawab_id' => $pj->id,
                'jumlah' => 150000,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Transaksi berhasil dibuat')
            ->assertJsonPath('data.deskripsi', 'Iuran Bulanan Pemuda')
            ->assertJsonMissingPath('data.bukti');

        $this->assertDatabaseHas('transaksi', [
            'deskripsi' => 'Iuran Bulanan Pemuda',
            'jenis_transaksi' => 'pemasukan',
            'jumlah' => 150000,
        ]);

        $this->assertDatabaseHas('riil_history', [
            'akun_id' => $akun->id,
            'date' => '2026-03-05',
            'riil' => 150000,
        ]);
    }

    public function test_bendahara_can_list_update_and_delete_transaksi(): void
    {
        $token = $this->getBendaharaToken();

        $akun = Akun::create([
            'nama_akun' => 'Kas Utama Pemuda',
            'kas' => 'kas pemuda',
        ]);

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/transaksi', [
                'deskripsi' => 'Beli Konsumsi Rapat',
                'date' => '2026-03-10',
                'jenis_transaksi' => 'pengeluaran',
                'akun_id' => $akun->id,
                'jumlah' => 75000,
            ]);

        $transaksiId = $createResponse->json('data.id');

        $listResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/transaksi?page=1&per_page=10&tanggal_mulai=2026-03-01&tanggal_selesai=2026-03-31&jenis_transaksi[]=pemasukan&jenis_transaksi[]=pengeluaran&kas[]=kas pemuda');

        $listResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['summary' => ['total_pemasukan', 'total_pengeluaran']]);

        $updateResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/transaksi/{$transaksiId}", [
                'deskripsi' => 'Beli Konsumsi Rapat Bulanan',
                'jumlah' => 100000,
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Transaksi berhasil diperbarui')
            ->assertJsonPath('data.deskripsi', 'Beli Konsumsi Rapat Bulanan');

        $deleteResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/transaksi/{$transaksiId}");

        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Transaksi berhasil dihapus');

        $this->assertSoftDeleted('transaksi', [
            'id' => $transaksiId,
        ]);
    }
}
