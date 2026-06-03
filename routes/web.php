<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MonitoringJadwalController;
use App\Http\Controllers\Admin\MonitoringLaporanController;
use App\Http\Controllers\Admin\PegawaiController as AdminPegawaiController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\Pegawai\JadwalKegiatanController as PegawaiJadwalKegiatanController;
use App\Http\Controllers\Pegawai\LaporanKegiatanController as PegawaiLaporanKegiatanController;
use App\Http\Controllers\Pegawai\PengajuanDinasController as PegawaiPengajuanDinasController;
use App\Http\Controllers\Pj\DashboardController as PjDashboardController;
use App\Http\Controllers\Pj\JadwalKegiatanController as PjJadwalKegiatanController;
use App\Http\Controllers\Pj\KegiatanController as PjKegiatanController;
use App\Http\Controllers\Pj\MonitoringLaporanController as PjMonitoringLaporanController;
use App\Http\Controllers\Pj\VerifikasiPengajuanDinasController as PjVerifikasiPengajuanDinasController;
use App\Http\Controllers\SearchController;
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
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/jadwal-kegiatan/export', [PegawaiJadwalKegiatanController::class, 'export'])->name('jadwal-kegiatan.export-global');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', AdminDashboardController::class)->name('dashboard');
            Route::get('/monitoring-jadwal', MonitoringJadwalController::class)->name('monitoring-jadwal');
            Route::get('/monitoring-laporan', [MonitoringLaporanController::class, 'index'])->name('monitoring-laporan');
            Route::get('/monitoring-laporan/export/{format}', [MonitoringLaporanController::class, 'export'])->name('monitoring-laporan.export');
            Route::get('/monitoring-laporan/{laporanKegiatan}', [MonitoringLaporanController::class, 'show'])->name('monitoring-laporan.show');
            Route::resource('pegawai', AdminPegawaiController::class)->except('show');
        });

    Route::prefix('pj')
        ->name('pj.')
        ->middleware('role:pj_penjadwalan')
        ->group(function () {
            Route::get('/', PjDashboardController::class)->name('dashboard');
            Route::get('/jadwal-kegiatan/referensi-ketersediaan', [PjJadwalKegiatanController::class, 'availability'])->name('jadwal-kegiatan.availability');
            Route::post('/jadwal-kegiatan/lepas-bentrok', [PjJadwalKegiatanController::class, 'releaseFromConflict'])->name('jadwal-kegiatan.release-from-conflict');
            Route::resource('jadwal-kegiatan', PjJadwalKegiatanController::class)->except('show');
            Route::resource('kegiatan', PjKegiatanController::class)->except('show');
            Route::redirect('/laporan-kegiatan', '/pj/monitoring-laporan')->name('laporan-kegiatan.legacy');
            Route::get('/monitoring-laporan', [PjMonitoringLaporanController::class, 'index'])->name('monitoring-laporan');
            Route::get('/monitoring-laporan/export/{format}', [PjMonitoringLaporanController::class, 'export'])->name('monitoring-laporan.export');
            Route::get('/monitoring-laporan/{laporanKegiatan}', [PjMonitoringLaporanController::class, 'show'])->name('monitoring-laporan.show');
            Route::get('/verifikasi-pengajuan-dinas', [PjVerifikasiPengajuanDinasController::class, 'index'])->name('verifikasi-pengajuan-dinas.index');
            Route::patch('/verifikasi-pengajuan-dinas/{pengajuanDina}', [PjVerifikasiPengajuanDinasController::class, 'update'])->name('verifikasi-pengajuan-dinas.update');
        });

    Route::prefix('pegawai')
        ->name('pegawai.')
        ->middleware('role:pegawai')
        ->group(function () {
            Route::get('/', PegawaiDashboardController::class)->name('dashboard');
            Route::get('/jadwal-kegiatan', PegawaiJadwalKegiatanController::class)->name('jadwal-kegiatan');
            Route::resource('laporan-kegiatan', PegawaiLaporanKegiatanController::class);
            Route::resource('pengajuan-dinas', PegawaiPengajuanDinasController::class)->except('show');
        });
});
