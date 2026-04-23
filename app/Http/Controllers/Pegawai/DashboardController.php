<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\PengajuanDinas;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $pegawai = auth()->user()->pegawai;
        $today = now()->toDateString();

        $upcomingSchedules = collect();
        $submissionCount = 0;
        $pendingSubmissionCount = 0;
        $approvedSubmissionCount = 0;
        $todayScheduleCount = 0;

        if ($pegawai) {
            $upcomingSchedules = $pegawai->jadwal()
                ->with('kegiatan')
                ->whereDate('tanggal', '>=', $today)
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->limit(3)
                ->get();

            $submissionCount = PengajuanDinas::where('pegawai_id', $pegawai->id)->count();
            $pendingSubmissionCount = PengajuanDinas::where('pegawai_id', $pegawai->id)
                ->where('status', 'diajukan')
                ->count();
            $approvedSubmissionCount = PengajuanDinas::where('pegawai_id', $pegawai->id)
                ->where('status', 'disetujui')
                ->count();
            $todayScheduleCount = $pegawai->jadwal()
                ->whereDate('tanggal', $today)
                ->count();
        }

        return view('pegawai.dashboard', [
            'upcomingSchedules' => $upcomingSchedules,
            'submissionCount' => $submissionCount,
            'pendingSubmissionCount' => $pendingSubmissionCount,
            'approvedSubmissionCount' => $approvedSubmissionCount,
            'todayScheduleCount' => $todayScheduleCount,
        ]);
    }
}
