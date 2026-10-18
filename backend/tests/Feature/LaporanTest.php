<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_laporan_keuangan(): void
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
            'deskripsi' => 'Donasi Warga',
            'date' => '2026-04-10',
            'jenis_transaksi' => 'pemasukan',
            'akun_id' => $akun->id,
            'jumlah' => 500000,
            'penginput_id' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/laporan?tanggal_mulai=2026-04-01&tanggal_selesai=2026-04-30&jenis_transaksi[]=pemasukan&jenis_transaksi[]=pengeluaran&kas=kas pemuda');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data laporan berhasil diambil')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'transaksi',
                    'mutasi',
                    'posisi_keuangan',
                ],
                'summary' => [
                    'total_pemasukan',
                    'total_pengeluaran',
                    'saldo_awal',
                    'kas_sekarang',
                ],
            ]);
    }

    public function test_authenticated_user_can_download_laporan_pdf(): void
    {
        $user = User::factory()->create([
            'role' => 'biasa',
            'status' => 'Aktif',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        Akun::create([
            'nama_akun' => 'Kas Utama Pemuda',
            'kas' => 'kas pemuda',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/laporan/pdf?tanggal_mulai=2026-04-01&tanggal_selesai=2026-04-30&jenis_transaksi[]=pemasukan&jenis_transaksi[]=pengeluaran&kas=kas pemuda');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }
}
