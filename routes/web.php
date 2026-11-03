<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Owner\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;

Route::get('/', fn () => view('home'))->name('home');

// Tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Hanya OWNER
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users');
    Route::patch('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approve');
    Route::patch('/users/{user}/reject', [UserManagementController::class, 'reject'])->name('users.reject');
    Route::patch('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
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