<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Dokter\ExportController as DokterExportController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\PembayaranController as PasienPembayaranController;
use App\Http\Controllers\Pasien\PoliController;
use App\Http\Controllers\Pasien\RiwayatPendaftaranController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ================= SEMUA ROLE (admin, dokter, pasien) =================
Route::middleware('auth')->group(function () {
    // Profile + ganti password
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Notifikasi (selalu dibatasi ke notifikasi milik pengguna yang login)
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');
    Route::get('/notifikasi/{id}', [NotificationController::class, 'buka'])->name('notifikasi.buka');
    Route::post('/notifikasi/{id}/baca', [NotificationController::class, 'baca'])->name('notifikasi.baca');
});

// ================= ADMIN ROUTES =================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Export Excel (didefinisikan sebelum resource agar tidak bentrok dengan {id})
    Route::get('/export/dokter', [AdminExportController::class, 'dokter'])->name('export.dokter');
    Route::get('/export/pasien', [AdminExportController::class, 'pasien'])->name('export.pasien');
    Route::get('/export/obat', [AdminExportController::class, 'obat'])->name('export.obat');

    // Verifikasi pembayaran
    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('admin.pembayaran.index');
    Route::post('/pembayaran/{pembayaran}/verifikasi', [AdminPembayaranController::class, 'verifikasi'])
        ->name('admin.pembayaran.verifikasi');

    Route::resource('polis', AdminPoliController::class);
    Route::resource('dokter', DokterController::class);
    Route::resource('pasien', PasienController::class);
    Route::resource('obat', ObatController::class);
});

// ================= DOKTER ROUTES =================
Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', function () {
        return view('dokter.dashboard');
    })->name('dokter.dashboard');

    // Export Excel
    Route::get('/export/jadwal-periksa', [DokterExportController::class, 'jadwalPeriksa'])->name('export.jadwal-periksa');
    Route::get('/export/riwayat-pasien', [DokterExportController::class, 'riwayatPasien'])->name('export.riwayat-pasien');

    Route::resource('jadwal-periksa', JadwalPeriksaController::class);

    Route::get('/periksa-pasien', [PeriksaPasienController::class, 'index'])
        ->name('periksa-pasien.index');

    Route::post('/periksa-pasien', [PeriksaPasienController::class, 'store'])
        ->name('periksa-pasien.store');

    Route::get('/periksa-pasien/{id}', [PeriksaPasienController::class, 'create'])
        ->name('periksa-pasien.create');

    Route::get('/riwayat-pasien', [RiwayatPasienController::class, 'index'])
        ->name('riwayat-pasien.index');

    Route::get('/riwayat-pasien/{id}', [RiwayatPasienController::class, 'show'])
        ->name('riwayat-pasien.show');
});

// ================= PASIEN ROUTES =================
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', [PasienDashboardController::class, 'index'])
        ->name('pasien.dashboard');

    Route::get('/daftar', [PoliController::class, 'get'])
        ->name('pasien.daftar');

    Route::post('/daftar', [PoliController::class, 'submit'])
        ->name('pasien.daftar.submit');

    // Riwayat pendaftaran poli
    Route::get('/riwayat-pendaftaran', [RiwayatPendaftaranController::class, 'index'])
        ->name('pasien.riwayat.index');

    Route::get('/riwayat-pendaftaran/{id}', [RiwayatPendaftaranController::class, 'show'])
        ->name('pasien.riwayat.show');

    // Pembayaran
    Route::get('/pembayaran', [PasienPembayaranController::class, 'index'])
        ->name('pasien.pembayaran.index');

    Route::post('/pembayaran/{pembayaran}/upload', [PasienPembayaranController::class, 'upload'])
        ->name('pasien.pembayaran.upload');
});
