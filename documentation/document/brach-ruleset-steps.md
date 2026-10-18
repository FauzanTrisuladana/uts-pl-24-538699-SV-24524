# Opsi B: Menggunakan Menu `Settings -> Rules -> Rulesets` (Satu Aturan untuk Keduanya)

1. Buka `Settings -> Rules -> Rulesets`, lalu klik `New ruleset -> New branch ruleset`.
2. Beri nama ruleset (misalnya: `Proteksi Main dan Dev`) dan ubah **Enforcement status** menjadi `Active`.
3. Pada bagian **Bypass list**, biarkan kosong agar tidak ada yang bisa menerobos aturan (tidak boleh push langsung).
4. Pada bagian **Target branches**, klik `Add target -> Include by pattern`, ketik `main` lalu klik `Add`, kemudian ulangi dan ketik `dev`.
5. Pada bagian **Branch rules**, centang:
   - `Require a pull request before merging`
   - `Require status checks to pass` (klik `+ Add checks` dan pilih nama job CI Anda).
6. Klik `Create` di bagian paling bawah.
