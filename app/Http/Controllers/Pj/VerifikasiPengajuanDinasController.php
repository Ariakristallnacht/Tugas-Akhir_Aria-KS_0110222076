<?php

namespace App\Http\Controllers\Pj;

use App\Http\Controllers\Controller;
use App\Models\PengajuanDinas;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VerifikasiPengajuanDinasController extends Controller
{
    public function index(): View
    {
        return view('pj.verifikasi-pengajuan-dinas.index', [
            'submissions' => PengajuanDinas::with(['pegawai', 'verifier'])
                ->latest('tanggal_pengajuan')
                ->latest('created_at')
                ->paginate(10),
            'summary' => [
                'all' => PengajuanDinas::count(),
                'pending' => PengajuanDinas::where('status', 'diajukan')->count(),
                'approved' => PengajuanDinas::where('status', 'disetujui')->count(),
                'rejected' => PengajuanDinas::where('status', 'ditolak')->count(),
            ],
        ]);
    }

    public function update(Request $request, PengajuanDinas $pengajuanDina): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'catatan_verifikasi' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($pengajuanDina, $validated) {
            $pengajuanDina->update([
                'status' => $validated['status'],
                'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
                'diverifikasi_oleh' => auth()->id(),
                'diverifikasi_at' => now(),
            ]);
        });

        return redirect()
            ->route('pj.verifikasi-pengajuan-dinas.index')
            ->with('success', 'Verifikasi Pengajuan Dinas berhasil disimpan.');
    }
}
