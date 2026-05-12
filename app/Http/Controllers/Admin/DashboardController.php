<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\LaporanKegiatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $recentReports = LaporanKegiatan::with(['pegawai', 'jadwal.kegiatan', 'pengajuanDinas'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'totalAccounts' => User::count(),
            'totalPegawai' => Pegawai::where('is_aktif', true)->count(),
            'totalLaporan' => LaporanKegiatan::count(),
            'laporanMenunggu' => LaporanKegiatan::where('status_verifikasi', 'menunggu')->count(),
            'jadwalHariIni' => Jadwal::whereDate('tanggal', today())->count(),
            'recentReports' => $recentReports,
        ]);
    }
}
