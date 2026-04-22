<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\PengajuanDinas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class JadwalKegiatanController extends Controller
{
    public function __invoke(Request $request): View
    {
        $pegawai = auth()->user()?->pegawai;
        abort_if(! $pegawai, 403, 'Akun pegawai belum terhubung dengan data pegawai.');

        $calendarMonthInput = $request->string('calendar_month')->toString() ?: now()->format('Y-m');
        $calendarMonth = $this->parseMonth($calendarMonthInput);
        $hasDateFromFilter = $request->filled('date_from');
        $hasDateToFilter = $request->filled('date_to');

        $referenceDate = now()->startOfDay();

        $dateFrom = $hasDateFromFilter
            ? Carbon::parse($request->string('date_from'))->startOfDay()
            : now()->startOfDay();

        $dateTo = $hasDateToFilter
            ? Carbon::parse($request->string('date_to'))->endOfDay()
            : now()->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $type = in_array($request->string('type')->toString(), ['all', 'layanan', 'dinas_luar'], true)
            ? $request->string('type')->toString()
            : 'all';

        $scope = in_array($request->string('scope')->toString(), ['mine', 'all'], true)
            ? $request->string('scope')->toString()
            : 'mine';

        $filteredItems = $this->buildAgendaItems($pegawai, $scope, $dateFrom, $dateTo, $referenceDate)
            ->when($type !== 'all', fn (Collection $collection) => $collection->where('type', $type))
            ->values();

        $calendarItems = $this->buildAgendaItems(
            $pegawai,
            'mine',
            $calendarMonth->copy()->startOfMonth(),
            $calendarMonth->copy()->endOfMonth(),
            $referenceDate
        );

        $calendarQuery = $request->except('calendar_month');

        return view('pegawai.jadwal-kegiatan.index', [
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'type' => $type,
                'scope' => $scope,
            ],
            'summary' => [
                'all' => $filteredItems->count(),
                'ongoing' => $filteredItems->where('phase', 'ongoing')->count(),
                'upcoming' => $filteredItems->where('phase', 'upcoming')->count(),
            ],
            'items' => $filteredItems,
            'calendarWeeks' => $this->buildCalendarWeeks($calendarMonth, $calendarItems, $referenceDate),
            'calendarMonthLabel' => $calendarMonth->translatedFormat('F Y'),
            'calendarFilters' => [
                'month' => $calendarMonth->format('Y-m'),
                'previous_month' => $calendarMonth->copy()->subMonth()->format('Y-m'),
                'next_month' => $calendarMonth->copy()->addMonth()->format('Y-m'),
                'current_month' => now()->format('Y-m'),
                'query' => $calendarQuery,
            ],
        ]);
    }

    private function buildAgendaItems($pegawai, string $scope, Carbon $dateFrom, Carbon $dateTo, Carbon $referenceDate): Collection
    {
        if ($scope === 'all') {
            $jadwalItems = Jadwal::with(['kegiatan', 'pegawai'])
                ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->get()
                ->map(fn (Jadwal $jadwal) => $this->mapAllJadwal($jadwal, $referenceDate));

            $dinasItems = PengajuanDinas::with('pegawai')
                ->whereIn('status', ['diajukan', 'disetujui'])
                ->where(function ($query) use ($dateFrom, $dateTo) {
                    $query->whereDate('tanggal_mulai', '<=', $dateTo->toDateString())
                        ->whereDate('tanggal_selesai', '>=', $dateFrom->toDateString());
                })
                ->orderBy('tanggal_mulai')
                ->orderBy('tanggal_selesai')
                ->get()
                ->map(fn (PengajuanDinas $dinas) => $this->mapAllDinas($dinas, $referenceDate));
        } else {
            $jadwalItems = $pegawai->jadwal()
                ->with('kegiatan')
                ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->get()
                ->map(fn (Jadwal $jadwal) => $this->mapJadwal($jadwal, $referenceDate, $pegawai->nama));

            $dinasItems = PengajuanDinas::query()
                ->where('pegawai_id', $pegawai->id)
                ->whereIn('status', ['diajukan', 'disetujui'])
                ->where(function ($query) use ($dateFrom, $dateTo) {
                    $query->whereDate('tanggal_mulai', '<=', $dateTo->toDateString())
                        ->whereDate('tanggal_selesai', '>=', $dateFrom->toDateString());
                })
                ->orderBy('tanggal_mulai')
                ->orderBy('tanggal_selesai')
                ->get()
                ->map(fn (PengajuanDinas $dinas) => $this->mapDinas($dinas, $referenceDate, $pegawai->nama));
        }

        return $jadwalItems
            ->concat($dinasItems)
            ->sortBy([
                ['phase_order', 'asc'],
                ['start_sort', 'asc'],
                ['title', 'asc'],
            ])
            ->values();
    }

    private function parseMonth(string $value): Carbon
    {
        return preg_match('/^\d{4}-\d{2}$/', $value) === 1
            ? Carbon::createFromFormat('Y-m', $value)->startOfMonth()
            : now()->startOfMonth();
    }

    private function mapJadwal(Jadwal $jadwal, Carbon $referenceDate, string $pegawaiName): array
    {
        $startDate = $jadwal->tanggal->copy()->startOfDay();
        $endDate = $jadwal->tanggal->copy()->endOfDay();
        $phase = $this->determinePhase($startDate, $endDate, $referenceDate);

        return [
            'key' => 'jadwal-'.$jadwal->id,
            'id' => $jadwal->id,
            'type' => 'layanan',
            'type_label' => 'Jadwal Layanan',
            'title' => $jadwal->kegiatan?->nama_kegiatan ?? 'Layanan',
            'subtitle' => $jadwal->lokasi,
            'description' => $jadwal->keterangan,
            'people' => $pegawaiName,
            'pj_initials' => $this->initials($pegawaiName),
            'date_label' => $startDate->translatedFormat('d M Y'),
            'time_label' => $this->formatTimeRange($jadwal->waktu_mulai, $jadwal->waktu_selesai),
            'phase' => $phase,
            'phase_label' => $this->phaseLabel($phase),
            'phase_order' => $this->phaseOrder($phase),
            'meta_status' => ucfirst($jadwal->pivot?->status_penugasan ?? $jadwal->status),
            'start_date' => $startDate->copy(),
            'end_date' => $endDate->copy(),
            'start_sort' => $startDate->copy()->setTimeFromTimeString($jadwal->waktu_mulai?->format('H:i:s') ?? '00:00:00')->timestamp,
        ];
    }

    private function mapDinas(PengajuanDinas $dinas, Carbon $referenceDate, string $pegawaiName): array
    {
        $startDate = $dinas->tanggal_mulai->copy()->startOfDay();
        $endDate = $dinas->tanggal_selesai->copy()->endOfDay();
        $phase = $this->determinePhase($startDate, $endDate, $referenceDate);

        return [
            'key' => 'dinas-'.$dinas->id,
            'id' => $dinas->id,
            'type' => 'dinas_luar',
            'type_label' => 'Dinas Luar',
            'title' => $dinas->kegiatan,
            'subtitle' => $dinas->tujuan,
            'description' => $dinas->keterangan,
            'people' => $pegawaiName,
            'pj_initials' => $this->initials($pegawaiName),
            'date_label' => $startDate->equalTo($endDate)
                ? $startDate->translatedFormat('d M Y')
                : $startDate->translatedFormat('d M Y').' - '.$endDate->translatedFormat('d M Y'),
            'time_label' => 'Seharian',
            'phase' => $phase,
            'phase_label' => $this->phaseLabel($phase),
            'phase_order' => $this->phaseOrder($phase),
            'meta_status' => ucfirst($dinas->status),
            'start_date' => $startDate->copy(),
            'end_date' => $endDate->copy(),
            'start_sort' => $startDate->timestamp,
        ];
    }

    private function mapAllJadwal(Jadwal $jadwal, Carbon $referenceDate): array
    {
        $startDate = $jadwal->tanggal->copy()->startOfDay();
        $endDate = $jadwal->tanggal->copy()->endOfDay();
        $phase = $this->determinePhase($startDate, $endDate, $referenceDate);
        $pegawaiNames = $jadwal->pegawai->pluck('nama');
        $displayName = $pegawaiNames->take(3)->implode(', ');
        $initialSeed = $pegawaiNames->first() ?: 'PG';

        return [
            'key' => 'jadwal-'.$jadwal->id,
            'id' => $jadwal->id,
            'type' => 'layanan',
            'type_label' => 'Jadwal Layanan',
            'title' => $jadwal->kegiatan?->nama_kegiatan ?? 'Layanan',
            'subtitle' => $jadwal->lokasi,
            'description' => $jadwal->keterangan,
            'people' => $displayName ?: 'Belum ada petugas',
            'pj_initials' => $this->initials($initialSeed),
            'date_label' => $startDate->translatedFormat('d M Y'),
            'time_label' => $this->formatTimeRange($jadwal->waktu_mulai, $jadwal->waktu_selesai),
            'phase' => $phase,
            'phase_label' => $this->phaseLabel($phase),
            'phase_order' => $this->phaseOrder($phase),
            'meta_status' => ucfirst($jadwal->status),
            'start_date' => $startDate->copy(),
            'end_date' => $endDate->copy(),
            'start_sort' => $startDate->copy()->setTimeFromTimeString($jadwal->waktu_mulai?->format('H:i:s') ?? '00:00:00')->timestamp,
        ];
    }

    private function mapAllDinas(PengajuanDinas $dinas, Carbon $referenceDate): array
    {
        $startDate = $dinas->tanggal_mulai->copy()->startOfDay();
        $endDate = $dinas->tanggal_selesai->copy()->endOfDay();
        $phase = $this->determinePhase($startDate, $endDate, $referenceDate);
        $pegawaiName = $dinas->pegawai?->nama ?? 'Pegawai tidak ditemukan';

        return [
            'key' => 'dinas-'.$dinas->id,
            'id' => $dinas->id,
            'type' => 'dinas_luar',
            'type_label' => 'Dinas Luar',
            'title' => $dinas->kegiatan,
            'subtitle' => $dinas->tujuan,
            'description' => $dinas->keterangan,
            'people' => $pegawaiName,
            'pj_initials' => $this->initials($pegawaiName),
            'date_label' => $startDate->equalTo($endDate)
                ? $startDate->translatedFormat('d M Y')
                : $startDate->translatedFormat('d M Y').' - '.$endDate->translatedFormat('d M Y'),
            'time_label' => 'Seharian',
            'phase' => $phase,
            'phase_label' => $this->phaseLabel($phase),
            'phase_order' => $this->phaseOrder($phase),
            'meta_status' => ucfirst($dinas->status),
            'start_date' => $startDate->copy(),
            'end_date' => $endDate->copy(),
            'start_sort' => $startDate->timestamp,
        ];
    }

    private function determinePhase(Carbon $startDate, Carbon $endDate, Carbon $referenceDate): string
    {
        if ($referenceDate->lt($startDate)) {
            return 'upcoming';
        }

        if ($referenceDate->gt($endDate)) {
            return 'completed';
        }

        return 'ongoing';
    }

    private function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'upcoming' => 'Belum Berlangsung',
            'ongoing' => 'Sedang Berlangsung',
            'completed' => 'Sudah Berlangsung',
            default => 'Tidak Diketahui',
        };
    }

    private function phaseOrder(string $phase): int
    {
        return match ($phase) {
            'ongoing' => 0,
            'upcoming' => 1,
            'completed' => 2,
            default => 3,
        };
    }

    private function formatTimeRange($start, $end): string
    {
        if (! $start && ! $end) {
            return 'Waktu fleksibel';
        }

        if ($start && $end) {
            return $start->format('H:i').' - '.$end->format('H:i');
        }

        return ($start?->format('H:i') ?? $end?->format('H:i')).' WIB';
    }

    private function initials(string $name): string
    {
        $parts = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)));

        return $parts->isNotEmpty() ? $parts->implode('') : 'PG';
    }

    private function buildCalendarWeeks(Carbon $monthDate, Collection $items, Carbon $referenceDate): array
    {
        $start = $monthDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $monthDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $cursor = $start->copy();
        $weeks = [];

        while ($cursor->lte($end)) {
            $week = [];

            for ($day = 0; $day < 7; $day++) {
                $currentDate = $cursor->copy();
                $dayItems = $items
                    ->filter(fn (array $item) => $currentDate->betweenIncluded($item['start_date'], $item['end_date']))
                    ->values();

                $week[] = [
                    'date' => $currentDate,
                    'in_month' => $currentDate->month === $monthDate->month,
                    'is_today' => $currentDate->isSameDay($referenceDate),
                    'count' => $dayItems->count(),
                    'items' => $dayItems->all(),
                    'preview_items' => $dayItems->take(2)->all(),
                    'layanan_count' => $dayItems->where('type', 'layanan')->count(),
                    'dinas_count' => $dayItems->where('type', 'dinas_luar')->count(),
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }
}
