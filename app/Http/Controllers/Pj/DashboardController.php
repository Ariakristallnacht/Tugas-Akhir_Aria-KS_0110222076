<?php

namespace App\Http\Controllers\Pj;

use App\Http\Controllers\Controller;
use App\Models\LaporanKegiatan;
use App\Models\PengajuanDinas;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $pendingSubmissions = PengajuanDinas::with('pegawai')
            ->where('status', 'diajukan')
            ->latest('tanggal_pengajuan')
            ->limit(5)
            ->get();

        return view('pj.dashboard', [
            'pendingCount' => PengajuanDinas::where('status', 'diajukan')->count(),
            'todaySubmissionCount' => PengajuanDinas::whereDate('tanggal_pengajuan', today())->count(),
            'reportCount' => LaporanKegiatan::count(),
            'pendingSubmissions' => $pendingSubmissions,
        ]);
    }
}
