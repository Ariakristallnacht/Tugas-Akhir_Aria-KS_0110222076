<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MonitoringJadwalController;
use App\Http\Controllers\Admin\PegawaiController as AdminPegawaiController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\Pj\DashboardController as PjDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->to(auth()->user()->dashboardPath());
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', AdminDashboardController::class)->name('dashboard');
            Route::get('/monitoring-jadwal', MonitoringJadwalController::class)->name('monitoring-jadwal');
            Route::resource('pegawai', AdminPegawaiController::class)->except('show');
        });

    Route::prefix('pj')
        ->name('pj.')
        ->middleware('role:pj_penjadwalan')
        ->group(function () {
            Route::get('/', PjDashboardController::class)->name('dashboard');
        });

    Route::prefix('pegawai')
        ->name('pegawai.')
        ->middleware('role:pegawai')
        ->group(function () {
            Route::get('/', PegawaiDashboardController::class)->name('dashboard');
        });
});
