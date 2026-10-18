<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\MutasiRekening;
use App\Models\RiilHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MutasiRekeningTest extends TestCase
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

    public function test_bendahara_can_create_mutasi_rekening_and_update_riil_history(): void
    {
        $token = $this->getBendaharaToken();

        $akunDebit = Akun::create([
            'nama_akun' => 'Bank BRI Pemuda',
            'kas' => 'kas pemuda',
        ]);
        $akunKredit = Akun::create([
            'nama_akun' => 'Kas Tunai Pemuda',
            'kas' => 'kas pemuda',
        ]);

        RiilHistory::create([
            'akun_id' => $akunKredit->id,
            'date' => '2026-02-01',
            'riil' => 1000000,
            'verified' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mutasi-rekening', [
                'date' => '2026-02-10',
                'kas' => 'kas pemuda',
                'akun_debit_id' => $akunDebit->id,
                'akun_kredit_id' => $akunKredit->id,
                'jumlah' => 250000,
                'keterangan' => 'Setor tunai ke BRI',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Mutasi rekening berhasil dibuat');

        $this->assertDatabaseHas('mutasi_rekening', [
            'akun_debit_id' => $akunDebit->id,
            'akun_kredit_id' => $akunKredit->id,
            'jumlah' => 250000,
        ]);

        $this->assertDatabaseHas('riil_history', [
            'akun_id' => $akunDebit->id,
            'date' => '2026-02-10',
            'riil' => 250000,
        ]);
        $this->assertDatabaseHas('riil_history', [
            'akun_id' => $akunKredit->id,
            'date' => '2026-02-10',
            'riil' => 750000,
        ]);
    }

    public function test_mutasi_rekening_validation_fails_for_same_debit_and_kredit_akun(): void
    {
        $token = $this->getBendaharaToken();

        $akun = Akun::create([
            'nama_akun' => 'Bank BRI Pemuda',
            'kas' => 'kas pemuda',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mutasi-rekening', [
                'date' => '2026-02-10',
                'kas' => 'kas pemuda',
                'akun_debit_id' => $akun->id,
                'akun_kredit_id' => $akun->id,
                'jumlah' => 100000,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors' => ['akun_kredit_id']]);
    }

    public function test_bendahara_can_list_update_and_delete_mutasi_rekening(): void
    {
        $token = $this->getBendaharaToken();

        $akunDebit = Akun::create([
            'nama_akun' => 'Bank BRI Pemuda',
            'kas' => 'kas pemuda',
        ]);
        $akunKredit = Akun::create([
            'nama_akun' => 'Kas Tunai Pemuda',
            'kas' => 'kas pemuda',
        ]);

        $mutasi = MutasiRekening::create([
            'date' => '2026-02-10',
            'kas' => 'kas pemuda',
            'akun_debit_id' => $akunDebit->id,
            'akun_kredit_id' => $akunKredit->id,
            'jumlah' => 200000,
            'keterangan' => 'Initial transfer',
        ]);

        $listResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mutasi-rekening?page=1&per_page=10&tanggal_mulai=2026-02-01&tanggal_selesai=2026-02-28&kas[]=kas pemuda');

        $listResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');

        $updateResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/mutasi-rekening/{$mutasi->id}", [
                'jumlah' => 300000,
                'keterangan' => 'Updated transfer',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Mutasi rekening berhasil diupdate');

        $deleteResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/mutasi-rekening/{$mutasi->id}");

        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Mutasi rekening berhasil dihapus');

        $this->assertSoftDeleted('mutasi_rekening', [
            'id' => $mutasi->id,
        ]);
    }
}
