<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Owner\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\KasirDashboardController;

Route::get('/', fn () => view('home'))->name('home');

// Tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::post('/notifikasi/baca', [NotifikasiController::class, 'baca'])
    ->middleware('auth')->name('notifikasi.baca');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Hanya OWNER
// Edit & hapus transaksi: hanya OWNER
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('owner.users');
    Route::put('/laporan/{transaction}', [LaporanController::class, 'update'])->name('laporan.update');
    Route::delete('/laporan/{transaction}', [LaporanController::class, 'destroy'])->name('laporan.destroy');

    // Harus di atas {user} supaya "hapus-massal" tidak dianggap id
    Route::delete('/users/hapus-massal', [UserManagementController::class, 'bulkDestroy'])->name('owner.users.bulk-destroy');

    Route::post('/users', [UserManagementController::class, 'store'])->name('owner.users.store');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('owner.users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('owner.users.destroy');

    Route::patch('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('owner.users.approve');
    Route::patch('/users/{user}/reject', [UserManagementController::class, 'reject'])->name('owner.users.reject');
});

// Dashboard: ADMIN (owner juga boleh)
// Dashboard + Data Barang: ADMIN (owner juga boleh)
Route::middleware(['auth', 'role:owner,admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Harus di atas resource supaya "hapus-massal" tidak dianggap {product}
    Route::delete('/barang/hapus-massal', [ProductController::class, 'bulkDestroy'])->name('barang.bulk-destroy');

    Route::resource('barang', ProductController::class)
        ->parameters(['barang' => 'product'])
        ->except(['create', 'show', 'edit']);

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
});

// POS: KASIR (owner juga boleh)
Route::middleware(['auth', 'role:owner,kasir'])->group(function () {
    Route::get('/pos', fn () => view('pos.index'))->name('pos.index');
});

// Pengaturan: OWNER & ADMIN
Route::middleware(['auth', 'role:owner,admin'])->prefix('pengaturan')->name('pengaturan.')->group(function () {
    Route::get('/', [PengaturanController::class, 'index'])->name('index');
    Route::put('/toko', [PengaturanController::class, 'updateToko'])->name('toko');
    Route::post('/gambar/{jenis}', [PengaturanController::class, 'uploadGambar'])
        ->whereIn('jenis', ['logo', 'banner'])->name('gambar');
    Route::put('/preferensi', [PengaturanController::class, 'preferensi'])->name('preferensi');
    Route::put('/profil', [PengaturanController::class, 'updateProfil'])->name('profil');
    Route::put('/password', [PengaturanController::class, 'updatePassword'])->name('password');
});

// Pengaturan sensitif: hanya OWNER
Route::middleware(['auth', 'role:owner'])->prefix('pengaturan')->name('pengaturan.')->group(function () {
    Route::put('/pembayaran', [PengaturanController::class, 'updatePembayaran'])->name('pembayaran');
    Route::get('/backup', [PengaturanController::class, 'backup'])->name('backup');
});

// Area KASIR
Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', [KasirDashboardController::class, 'index'])->name('dashboard');
});