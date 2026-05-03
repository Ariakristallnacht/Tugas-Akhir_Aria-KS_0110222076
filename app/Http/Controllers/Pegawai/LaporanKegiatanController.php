<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\LaporanKegiatan;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LaporanKegiatanController extends Controller
{
    public function index(Request $request): View
    {
        $pegawai = $this->currentPegawai();
        [$filters, $dateFrom, $dateTo] = $this->resolveFilters($request);

        $reportQuery = $this->buildQuery($pegawai, $filters, $dateFrom, $dateTo);
        $summaryReports = (clone $reportQuery)->get();
        $reports = $reportQuery
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('pegawai.laporan-kegiatan.index', [
            'filters' => $filters,
            'reports' => $reports,
            'summary' => [
                'all' => $summaryReports->count(),
                'kegiatan' => $summaryReports->pluck('jadwal.kegiatan_id')->filter()->unique()->count(),
                'bulan_ini' => $summaryReports->whereBetween('tanggal', [$dateFrom->copy()->startOfMonth(), $dateFrom->copy()->endOfMonth()])->count(),
                'dokumen' => $summaryReports->whereNotNull('dokumen_laporan_path')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $pegawai = $this->currentPegawai();
        $report = new LaporanKegiatan([
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->toDateString(),
            'status_verifikasi' => 'menunggu',
        ]);

        return view('pegawai.laporan-kegiatan.create', [
            'report' => $report,
            ...$this->formOptions($pegawai),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pegawai = $this->currentPegawai();
        $validated = $this->validateRequest($request, $pegawai);
        unset($validated['dokumen_laporan'], $validated['dokumen_laporan_existing']);

        $dokumenLaporan = $this->storeDokumenLaporan($request);

        LaporanKegiatan::create($validated + [
            'pegawai_id' => $pegawai->id,
            'dokumen_laporan_path' => $dokumenLaporan['path'] ?? null,
            'dokumen_laporan_nama' => $dokumenLaporan['name'] ?? null,
            'dokumen_laporan_mime' => $dokumenLaporan['mime'] ?? null,
        ]);

        return redirect()
            ->route('pegawai.laporan-kegiatan.index')
            ->with('success', 'Laporan kegiatan berhasil disimpan.');
    }

    public function show(LaporanKegiatan $laporanKegiatan): View
    {
        $pegawai = $this->currentPegawai();
        $this->ensureOwnReport($laporanKegiatan, $pegawai);

        return view('pegawai.laporan-kegiatan.show', [
            'report' => $laporanKegiatan->load(['jadwal.kegiatan', 'pegawai']),
        ]);
    }

    public function edit(LaporanKegiatan $laporanKegiatan): View
    {
        $pegawai = $this->currentPegawai();
        $this->ensureOwnReport($laporanKegiatan, $pegawai);

        return view('pegawai.laporan-kegiatan.edit', [
            'report' => $laporanKegiatan->load(['jadwal.pegawai', 'pegawai']),
            ...$this->formOptions($pegawai, $laporanKegiatan),
        ]);
    }

    public function update(Request $request, LaporanKegiatan $laporanKegiatan): RedirectResponse
    {
        $pegawai = $this->currentPegawai();
        $this->ensureOwnReport($laporanKegiatan, $pegawai);

        $validated = $this->validateRequest($request, $pegawai, $laporanKegiatan);
        unset($validated['dokumen_laporan'], $validated['dokumen_laporan_existing']);

        $dokumenLaporan = $this->storeDokumenLaporan($request);

        if ($dokumenLaporan && $laporanKegiatan->dokumen_laporan_path) {
            Storage::disk('public')->delete($laporanKegiatan->dokumen_laporan_path);
        }

        $laporanKegiatan->update($validated + [
            'pegawai_id' => $pegawai->id,
            'dokumen_laporan_path' => $dokumenLaporan['path'] ?? $laporanKegiatan->dokumen_laporan_path,
            'dokumen_laporan_nama' => $dokumenLaporan['name'] ?? $laporanKegiatan->dokumen_laporan_nama,
            'dokumen_laporan_mime' => $dokumenLaporan['mime'] ?? $laporanKegiatan->dokumen_laporan_mime,
        ]);

        return redirect()
            ->route('pegawai.laporan-kegiatan.index')
            ->with('success', 'Laporan kegiatan berhasil diperbarui.');
    }

    public function destroy(LaporanKegiatan $laporanKegiatan): RedirectResponse
    {
        $pegawai = $this->currentPegawai();
        $this->ensureOwnReport($laporanKegiatan, $pegawai);

        try {
            if ($laporanKegiatan->dokumen_laporan_path) {
                Storage::disk('public')->delete($laporanKegiatan->dokumen_laporan_path);
            }

            $laporanKegiatan->delete();
        } catch (QueryException) {
            return redirect()
                ->route('pegawai.laporan-kegiatan.index')
                ->with('error', 'Laporan kegiatan tidak dapat dihapus karena masih dipakai pada data lain.');
        }

        return redirect()
            ->route('pegawai.laporan-kegiatan.index')
            ->with('success', 'Laporan kegiatan berhasil dihapus.');
    }

    private function currentPegawai(): Pegawai
    {
        $pegawai = auth()->user()?->pegawai;
        abort_if(! $pegawai, 403, 'Akun pegawai belum terhubung dengan data pegawai.');

        return $pegawai;
    }

    private function ensureOwnReport(LaporanKegiatan $report, Pegawai $pegawai): void
    {
        abort_if((int) $report->pegawai_id !== (int) $pegawai->id, 403);
    }

    private function resolveFilters(Request $request): array
    {
        $monthInput = $request->string('month')->toString() ?: now()->format('Y-m');
        $monthDate = preg_match('/^\d{4}-\d{2}$/', $monthInput) === 1
            ? Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth()
            : now()->startOfMonth();

        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->string('date_from'))->startOfDay()
            : $monthDate->copy()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->string('date_to'))->endOfDay()
            : $monthDate->copy()->endOfMonth();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $filters = [
            'month' => $monthDate->format('Y-m'),
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'search' => trim($request->string('search')->toString()),
        ];

        return [$filters, $dateFrom, $dateTo];
    }

    private function validateRequest(Request $request, Pegawai $pegawai, ?LaporanKegiatan $report = null): array
    {
        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal,id'],
            'tanggal' => ['required', 'date'],
            'laporan' => ['nullable', 'string'],
            'dokumen_laporan' => ['required_without:dokumen_laporan_existing', 'file', 'mimes:pdf', 'max:10240'],
            'dokumen_laporan_existing' => ['nullable', 'string'],
        ]);

        $jadwal = Jadwal::with('pegawai')->findOrFail($validated['jadwal_id']);

        if (! $jadwal->pegawai->contains('id', $pegawai->id)) {
            throw ValidationException::withMessages([
                'jadwal_id' => 'Jadwal yang dipilih harus merupakan jadwal yang ditugaskan kepada Anda.',
            ]);
        }

        $validated['status_verifikasi'] = $report?->status_verifikasi ?? 'menunggu';
        $validated['catatan_verifikasi'] = $report?->catatan_verifikasi;
        $validated['diverifikasi_oleh'] = $report?->diverifikasi_oleh;
        $validated['diverifikasi_at'] = $report?->diverifikasi_at;

        return $validated;
    }

    private function storeDokumenLaporan(Request $request): ?array
    {
        if (! $request->hasFile('dokumen_laporan')) {
            return null;
        }

        $file = $request->file('dokumen_laporan');
        $path = $file->store('laporan-kegiatan/dokumen', 'public');

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
        ];
    }

    private function buildQuery(Pegawai $pegawai, array $filters, Carbon $dateFrom, Carbon $dateTo)
    {
        return LaporanKegiatan::query()
            ->with(['jadwal.kegiatan', 'pegawai'])
            ->where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $keyword = $filters['search'];

                $query->where(function ($nested) use ($keyword) {
                    $nested->whereHas('jadwal.kegiatan', fn ($kegiatanQuery) => $kegiatanQuery->where('nama_kegiatan', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('jadwal', fn ($jadwalQuery) => $jadwalQuery->where('lokasi', 'like', '%'.$keyword.'%'));
                });
            });
    }

    private function formOptions(Pegawai $pegawai, ?LaporanKegiatan $report = null): array
    {
        $jadwalOptions = $pegawai->jadwal()
            ->with(['kegiatan', 'pegawai'])
            ->where('status', '!=', 'dibatalkan')
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu_mulai')
            ->get();

        return [
            'jadwalOptions' => $jadwalOptions,
            'pegawaiOptions' => collect([$pegawai]),
            'lockedPegawai' => $pegawai,
            'lockPegawai' => true,
        ];
    }
}
