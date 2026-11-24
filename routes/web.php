<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirDashboardController;
use App\Http\Controllers\KasirLaporanController;
use App\Http\Controllers\KasirStokController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Owner\UserManagementController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

// ===== Tamu =====
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

// ===== Semua yang sudah login =====
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
});

// ===== OWNER saja =====
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('owner.users');
    Route::delete('/users/hapus-massal', [UserManagementController::class, 'bulkDestroy'])->name('owner.users.bulk-destroy');
    Route::post('/users', [UserManagementController::class, 'store'])->name('owner.users.store');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('owner.users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('owner.users.destroy');
    Route::patch('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('owner.users.approve');
    Route::patch('/users/{user}/reject', [UserManagementController::class, 'reject'])->name('owner.users.reject');

    Route::put('/laporan/{transaction}', [LaporanController::class, 'update'])->name('laporan.update');
    Route::delete('/laporan/{transaction}', [LaporanController::class, 'destroy'])->name('laporan.destroy');

    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::put('/pembayaran', [PengaturanController::class, 'updatePembayaran'])->name('pembayaran');
        Route::get('/backup', [PengaturanController::class, 'backup'])->name('backup');
        Route::post('/impor', [PengaturanController::class, 'impor'])->name('impor');
    });
});

// ===== OWNER & ADMIN =====
Route::middleware(['auth', 'role:owner,admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::delete('/barang/hapus-massal', [ProductController::class, 'bulkDestroy'])->name('barang.bulk-destroy');
    Route::resource('barang', ProductController::class)
        ->parameters(['barang' => 'product'])
        ->except(['create', 'show', 'edit']);

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'index'])->name('index');
        Route::put('/toko', [PengaturanController::class, 'updateToko'])->name('toko');
        Route::post('/gambar/{jenis}', [PengaturanController::class, 'uploadGambar'])
            ->whereIn('jenis', ['logo', 'banner'])->name('gambar');
        Route::put('/preferensi', [PengaturanController::class, 'preferensi'])->name('preferensi');
        Route::put('/profil', [PengaturanController::class, 'updateProfil'])->name('profil');
        Route::put('/password', [PengaturanController::class, 'updatePassword'])->name('password');
    });
});

// ===== POS: KASIR (owner juga boleh) =====
Route::middleware(['auth', 'role:owner,kasir'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::post('/pos/online', [PosController::class, 'online'])->name('pos.online');
    Route::get('/pos/lanjut/{transaction}', [PosController::class, 'lanjut'])->name('pos.lanjut');
    Route::get('/pos/status/{transaction}', [PosController::class, 'status'])->name('pos.status');
    Route::post('/pos/batal/{transaction}', [PosController::class, 'batal'])->name('pos.batal');
});

// ===== Struk: semua role (kasir hanya miliknya, dicek di controller) =====
Route::middleware(['auth', 'role:owner,admin,kasir'])->group(function () {
    Route::get('/pos/struk/{transaction}', [PosController::class, 'struk'])->name('pos.struk');
});

// ===== KASIR =====
Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', [KasirDashboardController::class, 'index'])->name('dashboard');
    Route::get('/stok', [KasirStokController::class, 'index'])->name('stok');
    Route::get('/laporan', [KasirLaporanController::class, 'index'])->name('laporan');
});