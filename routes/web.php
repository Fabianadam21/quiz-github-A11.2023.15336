<?php

use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController as AdminPasienController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dokter\DokterController as DokterWorkspaceController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Pasien\PasienController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('/notifikasi/{notification}/baca', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.read');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::resource('polis', AdminPoliController::class);
    Route::get('/dokter/{dokter}/jadwal', [DokterController::class, 'schedules'])->name('admin.dokter.jadwal');
    Route::post('/dokter/{dokter}/jadwal', [DokterController::class, 'storeSchedule'])->name('admin.dokter.jadwal.store');
    Route::resource('dokter', DokterController::class);
    Route::resource('pasien', AdminPasienController::class);
    Route::resource('obat', ObatController::class);
    Route::get('/export/dokter', [ExportController::class, 'dokter'])->name('admin.export.dokter');
    Route::get('/export/pasien', [ExportController::class, 'pasien'])->name('admin.export.pasien');
    Route::get('/export/obat', [ExportController::class, 'obat'])->name('admin.export.obat');
    Route::get('/pembayaran', [PembayaranController::class, 'adminIndex'])->name('admin.pembayaran.index');
    Route::get('/pembayaran/{periksa}/bukti', [PembayaranController::class, 'showProof'])->name('admin.pembayaran.proof');
    Route::patch('/pembayaran/{periksa}/verifikasi', [PembayaranController::class, 'verify'])->name('admin.pembayaran.verify');
});

Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', [DokterWorkspaceController::class, 'dashboard'])->name('dokter.dashboard');
    Route::get('/antrian', [DokterWorkspaceController::class, 'queue'])->name('dokter.antrian.index');
    Route::get('/riwayat-pasien', [DokterWorkspaceController::class, 'history'])->name('dokter.riwayat.index');
    Route::get('/antrian/{daftarPoli}/periksa', [DokterWorkspaceController::class, 'createExamination'])->name('dokter.periksa.create');
    Route::post('/antrian/{daftarPoli}/periksa', [DokterWorkspaceController::class, 'storeExamination'])->name('dokter.periksa.store');
    Route::get('/export/jadwal', [ExportController::class, 'jadwalDokter'])->name('dokter.export.jadwal');
    Route::get('/export/riwayat-pasien', [ExportController::class, 'riwayatDokter'])->name('dokter.export.riwayat');

    Route::resource('jadwal-periksa', JadwalPeriksaController::class);
});

Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', [PasienController::class, 'dashboard'])->name('pasien.dashboard');
    Route::get('/daftar-poli', [PasienController::class, 'createRegistration'])->name('pasien.daftar-poli.create');
    Route::post('/daftar-poli', [PasienController::class, 'storeRegistration'])->name('pasien.daftar-poli.store');
    Route::get('/riwayat-pendaftaran', [PasienController::class, 'history'])->name('pasien.riwayat.index');
    Route::get('/riwayat-pendaftaran/{daftarPoli}', [PasienController::class, 'historyDetail'])->name('pasien.riwayat.show');
    Route::get('/pembayaran', [PembayaranController::class, 'patientIndex'])->name('pasien.pembayaran.index');
    Route::post('/pembayaran/{periksa}/bukti', [PembayaranController::class, 'uploadProof'])->name('pasien.pembayaran.upload');
});
