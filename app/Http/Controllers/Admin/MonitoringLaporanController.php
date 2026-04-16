<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanKegiatan;
use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringLaporanController extends Controller
{
    public function index(Request $request): ViewContract
    {
        [$filters, $dateFrom, $dateTo] = $this->resolveFilters($request);

        $reportQuery = $this->buildQuery($filters, $dateFrom, $dateTo);
        $summaryReports = (clone $reportQuery)->get();
        $reports = $reportQuery
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.monitoring-laporan.index', [
            'filters' => $filters,
            'pegawaiOptions' => Pegawai::orderBy('nama')->get(['id', 'nama']),
            'reports' => $reports,
            'summary' => $this->buildSummary($summaryReports),
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

        $fileBaseName = 'monitoring-laporan-kegiatan-'.now()->format('Ymd-His');

        return match ($format) {
            'csv' => $this->exportCsv($reports, $fileBaseName.'.csv'),
            'xls' => $this->exportXls($reports, $filters, $fileBaseName.'.xls'),
            'pdf' => $this->exportPdf($reports, $filters, $fileBaseName.'.pdf'),
        };
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

    private function buildQuery(array $filters, Carbon $dateFrom, Carbon $dateTo): Builder
    {
        return LaporanKegiatan::query()
            ->with(['jadwal.kegiatan', 'pegawai'])
            ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->when($filters['pegawai_id'], fn (Builder $query, $pegawaiId) => $query->where('pegawai_id', $pegawaiId))
            ->when($filters['search'] !== '', function (Builder $query) use ($filters) {
                $keyword = $filters['search'];

                $query->where(function (Builder $nested) use ($keyword) {
                    $nested->where('laporan', 'like', '%'.$keyword.'%')
                        ->orWhereHas('pegawai', fn (Builder $pegawaiQuery) => $pegawaiQuery->where('nama', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('jadwal.kegiatan', fn (Builder $kegiatanQuery) => $kegiatanQuery->where('nama_kegiatan', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('jadwal', fn (Builder $jadwalQuery) => $jadwalQuery->where('lokasi', 'like', '%'.$keyword.'%'));
                });
            });
    }

    private function buildSummary(Collection $reports): array
    {
        return [
            'all' => $reports->count(),
            'pegawai' => $reports->pluck('pegawai_id')->filter()->unique()->count(),
            'kegiatan' => $reports->pluck('jadwal.kegiatan_id')->filter()->unique()->count(),
        ];
    }

    private function exportCsv(Collection $reports, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($reports) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Tanggal',
                'Pegawai',
                'Kegiatan',
                'Lokasi',
                'Waktu',
                'Status Jadwal',
                'Laporan',
            ]);

            foreach ($reports as $report) {
                fputcsv($handle, [
                    optional($report->tanggal)->translatedFormat('d/m/Y'),
                    $report->pegawai?->nama,
                    $report->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan tidak ditemukan',
                    $report->jadwal?->lokasi ?? '-',
                    trim(($report->jadwal?->waktu_mulai?->format('H:i') ?? '-').' - '.($report->jadwal?->waktu_selesai?->format('H:i') ?? '-')),
                    ucfirst($report->jadwal?->status ?? 'tidak diketahui'),
                    $report->laporan,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportXls(Collection $reports, array $filters, string $filename): Response
    {
        $content = view('admin.monitoring-laporan.exports.xls', [
            'reports' => $reports,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function exportPdf(Collection $reports, array $filters, string $filename)
    {
        return Pdf::loadView('admin.monitoring-laporan.exports.pdf', [
            'reports' => $reports,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download($filename);
    }
}
