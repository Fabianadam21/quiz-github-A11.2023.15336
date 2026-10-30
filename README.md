# Quiz GitHub - Poliklinik App

## Identitas

- **Nama:** Fabian Adam Maheswara
- **NIM:** A11.2023.15336
- **Kelas:** DEV-02
- **Mata Kuliah:** Bengkel Koding

## Deskripsi

Repository ini berisi project aplikasi Poliklinik yang dikembangkan sebagai bagian dari tugas mata kuliah Bengkel Koding. Project ini menggunakan Laravel dan mencakup pengelolaan data pengguna, poli, jadwal pemeriksaan, pendaftaran pasien, pemeriksaan, obat, dan detail pemeriksaan.

## Fitur

- Dashboard pasien menampilkan jadwal dokter dan antrean aktif yang diperbarui melalui Laravel Reverb dan Echo.
- Pendaftaran poli membatasi pasien pada satu pemeriksaan yang belum selesai.
- Riwayat pendaftaran pasien dan detail pemeriksaan dibatasi pada pemilik data.
- Notifikasi database untuk unggahan bukti pembayaran dan pembayaran yang telah diverifikasi.
- Upload bukti pembayaran oleh pasien dan verifikasi pembayaran oleh admin.
- Export Excel data dokter, pasien, obat (termasuk stok dan harga), jadwal dokter, dan riwayat pasien dokter.
- Ganti password dengan validasi password lama, konfirmasi, panjang minimal, dan penyimpanan hash.

## Menjalankan Fitur

1. Salin `.env.example` ke `.env`, lalu isi konfigurasi database dan jalankan `php artisan key:generate`.
2. Pasang dependency PHP dan JavaScript: `composer install` dan `npm install`.
3. Siapkan Reverb dan kunci aplikasinya dengan `php artisan reverb:install`. Pastikan `BROADCAST_CONNECTION=reverb`.
4. Jalankan migrasi: `php artisan migrate`.
5. Jalankan aplikasi dan server websocket pada terminal terpisah:
   - `php artisan serve`
   - `php artisan reverb:start`
   - `npm run dev` (atau `npm run build` untuk asset production)

File dokumentasi pengumpulan tersedia pada [feature-documentation.pdf](./feature-documentation.pdf).
