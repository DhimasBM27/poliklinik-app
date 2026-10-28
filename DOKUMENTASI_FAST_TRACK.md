# Dokumentasi Fitur Fast Track – Poliklinik App

## A. Persiapan setelah menimpa file

```powershell
composer require maatwebsite/excel laravel/reverb
npm install --legacy-peer-deps
php artisan migrate
php artisan storage:link
php artisan optimize:clear
```

Tambahkan ke `.env` (contoh ada di `.env.example`):

```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=poliklinik
REVERB_APP_KEY=poliklinik-key
REVERB_APP_SECRET=poliklinik-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Menjalankan aplikasi (3 terminal):

```powershell
php artisan serve
php artisan reverb:start
npm run dev
```

## B. Fitur

| Fitur | Ringkasan | File utama |
|---|---|---|
| Dashboard Pasien | Banner antrian aktif (poli, dokter, jadwal, nomor anda, nomor dilayani) + tabel jadwal. Nomor dilayani live. | `Pasien/DashboardController`, `pasien/dashboard.blade.php` |
| Sistem Antrian Real-Time | Event `AntrianDiperbarui` disiarkan lewat Laravel Reverb ke channel `antrian`; Laravel Echo memperbarui angka tanpa reload. | `Events/AntrianDiperbarui`, `resources/js/echo.js`, `config/reverb.php`, `config/broadcasting.php` |
| Notifikasi | Lonceng + dropdown di navbar, badge belum dibaca, tandai dibaca / semua dibaca. 2 jenis: pembayaran diunggah (ke dokter) dan pembayaran diverifikasi (ke pasien). Database channel. | `Notifications/*`, `NotificationController`, `partials/header.blade.php` |
| Riwayat Pendaftaran Poli | Daftar pendaftaran pasien (terbaru dulu) + detail (catatan dokter, obat, total biaya). | `Pasien/RiwayatPendaftaranController`, `pasien/riwayat/*` |
| Export Excel | Admin: dokter, pasien, obat (dengan stok & harga). Dokter: jadwal periksa & riwayat pasien miliknya. Memakai maatwebsite/excel. | `Exports/*`, `Admin/ExportController`, `Dokter/ExportController` |
| Upload Bukti Pembayaran | Tagihan otomatis dibuat saat pemeriksaan disimpan. Pasien upload foto bukti; admin verifikasi -> Lunas. | `Pasien/PembayaranController`, `Admin/PembayaranController`, `Models/Pembayaran` |
| Ganti Password | Menu Profile untuk semua role, divalidasi lewat Form Request. | `ProfileController`, `Requests/UpdatePasswordRequest`, `profile/edit.blade.php` |

## C. Constraint teknis yang diimplementasikan

- **Satu antrian aktif**: pasien tidak bisa mendaftar lagi sebelum pemeriksaan selesai (dicek di dalam transaksi + `lockForUpdate` pada baris pasien agar klik ganda aman).
- **Nomor dilayani** = nomor terkecil pada sebuah jadwal yang belum diperiksa. Dihitung dengan satu query `GROUP BY` untuk seluruh tabel jadwal.
- **Broadcast aman gagal**: `AntrianDiperbarui::kirim()` memakai `rescue()`; bila server Reverb mati, kegagalan hanya dicatat di log dan pendaftaran/pemeriksaan tetap berhasil.
- **Notifikasi tidak ganda**: perubahan status memakai update bersyarat (`WHERE status = ...`), notifikasi hanya dikirim bila update benar-benar mengubah satu baris.
- **Notifikasi privat**: selalu dicari lewat `Auth::user()->notifications()` sehingga milik orang lain menghasilkan 404.
- **Pembayaran**: hanya pemilik tagihan yang bisa upload (403 untuk pasien lain); file gambar JPG/PNG maks. 2 MB disimpan di disk `public`; hanya admin yang bisa verifikasi.
- **Stok obat**: kolom `stok` pada obat; stok berkurang saat obat diresepkan, pemeriksaan dibatalkan bila stok habis (transaksi database).
- **Ganti password**: password lama dicek dengan rule `current_password`; baru minimal 8 karakter, harus sama dengan konfirmasi, dan `different:current_password`; disimpan dalam bentuk hash; hanya akun yang sedang login yang diubah.
- **Efisiensi query**: relasi dimuat dengan eager loading (`with(...)`) untuk menghindari N+1.

## D. Pengujian

`php artisan test` menjalankan `tests/Feature/FastTrackTest.php` (26 test) di database sqlite in-memory: aturan antrian, event real-time, tagihan & stok, alur pembayaran, notifikasi (satu kejadian = satu notifikasi, privasi), ganti password, dan data export.
