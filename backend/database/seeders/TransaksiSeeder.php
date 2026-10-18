<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transaksis = [
            ['id' => 1, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Saldo Awal', 'date' => '2026-05-05', 'jenis_transaksi' => 'pemasukan', 'jumlah' => 5400000],
            ['id' => 2, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Kado Lahiran', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 195000],
            ['id' => 3, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Bakar2 tahun baru ikan 15kg', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 525000],
            ['id' => 4, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Bakar2 tahun baru bumbu', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 318000],
            ['id' => 5, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Baksos', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 300000],
            ['id' => 6, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Bukber', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 200000],
            ['id' => 7, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Roti bakar konsum rapat', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 30000],
            ['id' => 8, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Dp bukber', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 500000],
            ['id' => 9, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Supir', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 150000],
            ['id' => 10, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Bensin', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 200000],
            ['id' => 11, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Pelunasan Bukber', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 1018000],
            ['id' => 12, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Parkir', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 10000],
            ['id' => 13, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Bensin', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 200000],
            ['id' => 14, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Konsum rapat 17 an petama', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 28000],
            ['id' => 15, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Konsum rapat 17 an ketiga', 'date' => '2026-05-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 43000],
            ['id' => 16, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Iuran pemuda', 'date' => '2026-05-05', 'jenis_transaksi' => 'pemasukan', 'jumlah' => 440000],
            ['id' => 17, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Pemasukan Pemuda', 'date' => '2026-05-05', 'jenis_transaksi' => 'pemasukan', 'jumlah' => 190000],
            ['id' => 18, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Pemasukan Pemuda', 'date' => '2026-05-06', 'jenis_transaksi' => 'pemasukan', 'jumlah' => 190000],
            ['id' => 19, 'akun_id' => 1, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Konsum Rapat 17 an 23 Mei', 'date' => '2026-05-23', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 29000],
            ['id' => 20, 'akun_id' => 2, 'penginput_id' => 1, 'penanggung_jawab_id' => 4, 'deskripsi' => 'Bumbu Bakar2 Kurban', 'date' => '2026-05-28', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 45000],
            ['id' => 21, 'akun_id' => 2, 'penginput_id' => 1, 'penanggung_jawab_id' => 4, 'deskripsi' => 'Masak Masak 30 Mei', 'date' => '2026-05-30', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 75000],
            ['id' => 22, 'akun_id' => 3, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Snack sosialisasi 17 an', 'date' => '2026-06-05', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 262500],
            ['id' => 23, 'akun_id' => 3, 'penginput_id' => 1, 'penanggung_jawab_id' => 2, 'deskripsi' => 'Print proposal dan materi', 'date' => '2026-06-06', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 80000],
            ['id' => 24, 'akun_id' => 3, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Beli air sosialisasi', 'date' => '2026-06-14', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 20000],
            ['id' => 25, 'akun_id' => 3, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Beli konsumsi rapat 13 juni 2026', 'date' => '2026-06-14', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 14000],
            ['id' => 26, 'akun_id' => 2, 'penginput_id' => 1, 'penanggung_jawab_id' => 4, 'deskripsi' => 'Pengeluaran masak2', 'date' => '2026-06-15', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 50000],
            ['id' => 27, 'akun_id' => 2, 'penginput_id' => 1, 'penanggung_jawab_id' => 3, 'deskripsi' => 'Beli karpet ruang pemuda', 'date' => '2026-06-15', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 55000],
            ['id' => 28, 'akun_id' => 3, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Konsumsi rapat', 'date' => '2026-06-20', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 315000],
            ['id' => 29, 'akun_id' => 3, 'penginput_id' => 1, 'penanggung_jawab_id' => 1, 'deskripsi' => 'Konsum rapat proposal dan konsum rapat pensi', 'date' => '2026-06-21', 'jenis_transaksi' => 'pengeluaran', 'jumlah' => 64000],
        ];

        foreach ($transaksis as $transaksi) {
            Transaksi::create($transaksi);
        }

        Transaksi::findOrFail(17)->delete();
    }
}
