<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Owner\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('auth.login'))->name('auth.login');

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
Route::middleware(['auth', 'role:owner,admin'])->group(function () {
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
});

// POS: KASIR (owner juga boleh)
Route::middleware(['auth', 'role:owner,kasir'])->group(function () {
    Route::get('/pos', fn () => view('pos.index'))->name('pos.index');
});