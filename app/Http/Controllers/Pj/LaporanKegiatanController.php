<?php

namespace App\Http\Controllers\Pj;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\LaporanKegiatan;
use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanKegiatanController extends Controller
{
    public function index(Request $request): View
    {
        [$filters, $dateFrom, $dateTo] = $this->resolveFilters($request);

        $reportQuery = $this->buildQuery($filters, $dateFrom, $dateTo);

        $summaryReports = (clone $reportQuery)->get();
        $reports = $reportQuery
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('pj.laporan-kegiatan.index', [
            'filters' => $filters,
            'pegawaiOptions' => Pegawai::orderBy('nama')->get(['id', 'nama']),
            'reports' => $reports,
            'summary' => [
                'all' => $summaryReports->count(),
                'pegawai' => $summaryReports->pluck('pegawai_id')->filter()->unique()->count(),
                'kegiatan' => $summaryReports->pluck('jadwal.kegiatan_id')->filter()->unique()->count(),
                'bulan_ini' => $summaryReports->whereBetween('tanggal', [$dateFrom->copy()->startOfMonth(), $dateFrom->copy()->endOfMonth()])->count(),
            ],
        ]);
    }

    public function export(Request $request, string $format)
    {
        [$filters, $dateFrom, $dateTo] = $this->resolveFilters($request);

        abort_unless(in_array($format, ['csv', 'xls', 'pdf'], true), 404);

        $reports = $this->buildQuery($filters, $dateFrom, $dateTo)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $fileBaseName = 'laporan-kegiatan-pj-'.now()->format('Ymd-His');

        return match ($format) {
            'csv' => $this->exportCsv($reports, $fileBaseName.'.csv'),
            'xls' => $this->exportXls($reports, $filters, $fileBaseName.'.xls'),
            'pdf' => $this->exportPdf($reports, $filters, $fileBaseName.'.pdf'),
        };
    }

    public function create(): View
    {
        $report = new LaporanKegiatan([
            'tanggal' => now()->toDateString(),
            'status_verifikasi' => 'menunggu',
        ]);

        return view('pj.laporan-kegiatan.create', [
            'report' => $report,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        unset($validated['dokumen_laporan'], $validated['dokumen_laporan_existing']);
        $dokumenLaporan = $this->storeDokumenLaporan($request);

        LaporanKegiatan::create($validated + [
            'dokumen_laporan_path' => $dokumenLaporan['path'] ?? null,
            'dokumen_laporan_nama' => $dokumenLaporan['name'] ?? null,
            'dokumen_laporan_mime' => $dokumenLaporan['mime'] ?? null,
        ]);

        return redirect()
            ->route('pj.laporan-kegiatan.index')
            ->with('success', 'Laporan kegiatan berhasil disimpan.');
    }

    public function edit(LaporanKegiatan $laporanKegiatan): View
    {
        return view('pj.laporan-kegiatan.edit', [
            'report' => $laporanKegiatan->load(['jadwal.pegawai', 'pegawai']),
            ...$this->formOptions($laporanKegiatan),
        ]);
    }

    public function update(Request $request, LaporanKegiatan $laporanKegiatan): RedirectResponse
    {
        $validated = $this->validateRequest($request, $laporanKegiatan);
        unset($validated['dokumen_laporan'], $validated['dokumen_laporan_existing']);
        $dokumenLaporan = $this->storeDokumenLaporan($request);

        if ($dokumenLaporan && $laporanKegiatan->dokumen_laporan_path) {
            Storage::disk('public')->delete($laporanKegiatan->dokumen_laporan_path);
        }

        $laporanKegiatan->update($validated + [
            'dokumen_laporan_path' => $dokumenLaporan['path'] ?? $laporanKegiatan->dokumen_laporan_path,
            'dokumen_laporan_nama' => $dokumenLaporan['name'] ?? $laporanKegiatan->dokumen_laporan_nama,
            'dokumen_laporan_mime' => $dokumenLaporan['mime'] ?? $laporanKegiatan->dokumen_laporan_mime,
        ]);

        return redirect()
            ->route('pj.laporan-kegiatan.index')
            ->with('success', 'Laporan kegiatan berhasil diperbarui.');
    }

    public function destroy(LaporanKegiatan $laporanKegiatan): RedirectResponse
    {
        try {
            if ($laporanKegiatan->dokumen_laporan_path) {
                Storage::disk('public')->delete($laporanKegiatan->dokumen_laporan_path);
            }

            $laporanKegiatan->delete();
        } catch (QueryException) {
            return redirect()
                ->route('pj.laporan-kegiatan.index')
                ->with('error', 'Laporan kegiatan tidak dapat dihapus karena masih dipakai pada data lain.');
        }

        return redirect()
            ->route('pj.laporan-kegiatan.index')
            ->with('success', 'Laporan kegiatan berhasil dihapus.');
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

        $pegawaiId = $request->integer('pegawai_id');
        $filters = [
            'month' => $monthDate->format('Y-m'),
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'pegawai_id' => $pegawaiId > 0 ? $pegawaiId : null,
            'search' => trim($request->string('search')->toString()),
        ];

        return [$filters, $dateFrom, $dateTo];
    }

    private function validateRequest(Request $request, ?LaporanKegiatan $report = null): array
    {
        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal,id'],
            'pegawai_id' => ['required', 'exists:pegawai,id'],
            'tanggal' => ['required', 'date'],
            'laporan' => ['required', 'string'],
            'dokumen_laporan' => ['required_without:dokumen_laporan_existing', 'file', 'mimes:pdf', 'max:10240'],
            'dokumen_laporan_existing' => ['nullable', 'string'],
        ]);

        $jadwal = Jadwal::with('pegawai')->findOrFail($validated['jadwal_id']);

        if (! $jadwal->pegawai->contains('id', (int) $validated['pegawai_id'])) {
            throw ValidationException::withMessages([
                'pegawai_id' => 'Pegawai yang dipilih harus merupakan petugas pada jadwal kegiatan tersebut.',
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

    private function buildQuery(array $filters, Carbon $dateFrom, Carbon $dateTo)
    {
        return LaporanKegiatan::query()
            ->with(['jadwal.kegiatan', 'pegawai'])
            ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->when($filters['pegawai_id'], fn ($query, $pegawaiId) => $query->where('pegawai_id', $pegawaiId))
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $keyword = $filters['search'];

                $query->where(function ($nested) use ($keyword) {
                    $nested->where('laporan', 'like', '%'.$keyword.'%')
                        ->orWhereHas('pegawai', fn ($pegawaiQuery) => $pegawaiQuery->where('nama', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('jadwal.kegiatan', fn ($kegiatanQuery) => $kegiatanQuery->where('nama_kegiatan', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('jadwal', fn ($jadwalQuery) => $jadwalQuery->where('lokasi', 'like', '%'.$keyword.'%'));
                });
            });
    }

    private function exportCsv($reports, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($reports) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Tanggal',
                'Pegawai',
                'Kegiatan',
                'Lokasi',
                'Waktu',
                'Laporan',
            ]);

            foreach ($reports as $report) {
                fputcsv($handle, [
                    optional($report->tanggal)->translatedFormat('d/m/Y'),
                    $report->pegawai?->nama,
                    $report->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan tidak ditemukan',
                    $report->jadwal?->lokasi ?? '-',
                    trim(($report->jadwal?->waktu_mulai?->format('H:i') ?? '-').' - '.($report->jadwal?->waktu_selesai?->format('H:i') ?? '-')),
                    $report->laporan,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportXls($reports, array $filters, string $filename): Response
    {
        $content = view('pj.laporan-kegiatan.exports.xls', [
            'reports' => $reports,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function exportPdf($reports, array $filters, string $filename)
    {
        return Pdf::loadView('pj.laporan-kegiatan.exports.pdf', [
            'reports' => $reports,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download($filename);
    }

    private function formOptions(?LaporanKegiatan $report = null): array
    {
        $jadwalOptions = Jadwal::with(['kegiatan', 'pegawai'])
            ->where('status', '!=', 'dibatalkan')
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu_mulai')
            ->get();

        $selectedJadwalId = old('jadwal_id', $report?->jadwal_id);
        $selectedJadwal = $selectedJadwalId ? $jadwalOptions->firstWhere('id', (int) $selectedJadwalId) : null;

        return [
            'jadwalOptions' => $jadwalOptions,
            'pegawaiOptions' => $selectedJadwal?->pegawai?->sortBy('nama')->values() ?? collect(),
        ];
    }
}
