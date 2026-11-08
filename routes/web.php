<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Owner\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LaporanController;

Route::get('/', fn () => view('home'))->name('home');

// Tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

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

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
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
});

// POS: KASIR (owner juga boleh)
Route::middleware(['auth', 'role:owner,kasir'])->group(function () {
    Route::get('/pos', fn () => view('pos.index'))->name('pos.index');
});