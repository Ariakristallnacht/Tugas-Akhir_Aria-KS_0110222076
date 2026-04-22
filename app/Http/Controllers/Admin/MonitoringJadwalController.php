<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\PengajuanDinas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MonitoringJadwalController extends Controller
{
    public function __invoke(Request $request): View
    {
        $calendarMonthInput = $request->string('calendar_month')->toString() ?: now()->format('Y-m');
        $monthDate = $this->parseMonth($calendarMonthInput);

        $referenceDate = now()->startOfDay();
        $defaultDate = $this->resolveDefaultDate($referenceDate);

        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->string('date_from'))->startOfDay()
            : $defaultDate->copy()->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->string('date_to'))->endOfDay()
            : $defaultDate->copy()->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $type = in_array($request->string('type')->toString(), ['all', 'layanan', 'dinas_luar'], true)
            ? $request->string('type')->toString()
            : 'all';

        $phase = in_array($request->string('phase')->toString(), ['all', 'upcoming', 'ongoing', 'completed'], true)
            ? $request->string('phase')->toString()
            : 'all';

        $items = $this->buildAgendaItems($dateFrom, $dateTo, $referenceDate);

        $filteredItems = $items
            ->when($type !== 'all', fn (Collection $collection) => $collection->where('type', $type))
            ->when($phase !== 'all', fn (Collection $collection) => $collection->where('phase', $phase))
            ->values();

        $calendarItems = $this->buildAgendaItems(
            $monthDate->copy()->startOfMonth(),
            $monthDate->copy()->endOfMonth(),
            $referenceDate
        );

        $calendarQuery = $request->except('calendar_month');

        return view('admin.monitoring-jadwal.index', [
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'type' => $type,
                'phase' => $phase,
            ],
            'summary' => [
                'all' => $items->count(),
                'upcoming' => $items->where('phase', 'upcoming')->count(),
                'ongoing' => $items->where('phase', 'ongoing')->count(),
                'completed' => $items->where('phase', 'completed')->count(),
            ],
            'items' => $filteredItems,
            'calendarWeeks' => $this->buildCalendarWeeks($monthDate, $calendarItems, $referenceDate),
            'calendarMonthLabel' => $monthDate->translatedFormat('F Y'),
            'calendarFilters' => [
                'month' => $monthDate->format('Y-m'),
                'previous_month' => $monthDate->copy()->subMonth()->format('Y-m'),
                'next_month' => $monthDate->copy()->addMonth()->format('Y-m'),
                'current_month' => now()->format('Y-m'),
                'query' => $calendarQuery,
            ],
        ]);
    }

    private function parseMonth(string $value): Carbon
    {
        return preg_match('/^\d{4}-\d{2}$/', $value) === 1
            ? Carbon::createFromFormat('Y-m', $value)->startOfMonth()
            : now()->startOfMonth();
    }

    private function buildAgendaItems(Carbon $dateFrom, Carbon $dateTo, Carbon $referenceDate): Collection
    {
        $jadwalItems = Jadwal::with(['kegiatan', 'pegawai'])
            ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->get()
            ->map(fn (Jadwal $jadwal) => $this->mapJadwal($jadwal, $referenceDate));

        $dinasItems = PengajuanDinas::with('pegawai')
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->whereDate('tanggal_mulai', '<=', $dateTo->toDateString())
                    ->whereDate('tanggal_selesai', '>=', $dateFrom->toDateString());
            })
            ->orderBy('tanggal_mulai')
            ->orderBy('tanggal_selesai')
            ->get()
            ->map(fn (PengajuanDinas $dinas) => $this->mapDinas($dinas, $referenceDate));

        return $jadwalItems
            ->concat($dinasItems)
            ->sortBy([
                ['phase_order', 'asc'],
                ['start_sort', 'asc'],
                ['title', 'asc'],
            ])
            ->values();
    }

    private function resolveDefaultDate(Carbon $referenceDate): Carbon
    {
        if (
            Jadwal::query()->whereDate('tanggal', $referenceDate->toDateString())->exists()
            || PengajuanDinas::query()
                ->whereDate('tanggal_mulai', '<=', $referenceDate->toDateString())
                ->whereDate('tanggal_selesai', '>=', $referenceDate->toDateString())
                ->exists()
        ) {
            return $referenceDate->copy();
        }

        $candidates = collect()
            ->merge(Jadwal::query()->pluck('tanggal')->map(fn ($date) => Carbon::parse($date)->startOfDay()))
            ->merge(PengajuanDinas::query()->pluck('tanggal_mulai')->map(fn ($date) => Carbon::parse($date)->startOfDay()))
            ->merge(PengajuanDinas::query()->pluck('tanggal_selesai')->map(fn ($date) => Carbon::parse($date)->startOfDay()))
            ->filter()
            ->sortBy(fn (Carbon $date) => abs($date->diffInDays($referenceDate, false)))
            ->values();

        return $candidates->first()?->copy() ?? $referenceDate->copy();
    }

    private function mapJadwal(Jadwal $jadwal, Carbon $referenceDate): array
    {
        $startDate = $jadwal->tanggal->copy()->startOfDay();
        $endDate = $jadwal->tanggal->copy()->endOfDay();
        $phase = $this->determinePhase($startDate, $endDate, $referenceDate);
        $firstPegawaiName = $jadwal->pegawai->pluck('nama')->first();
        $pegawaiNames = $jadwal->pegawai->pluck('nama')->take(3)->implode(', ');

        return [
            'key' => 'jadwal-'.$jadwal->id,
            'id' => $jadwal->id,
            'type' => 'layanan',
            'type_label' => 'Jadwal Layanan',
            'title' => $jadwal->kegiatan?->nama_kegiatan ?? 'Layanan',
            'subtitle' => $jadwal->lokasi,
            'description' => $jadwal->keterangan,
            'people' => $pegawaiNames ?: 'Belum ada petugas',
            'pj_initials' => $this->initials($firstPegawaiName ?: 'PJ'),
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

    private function mapDinas(PengajuanDinas $dinas, Carbon $referenceDate): array
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
            'people' => $dinas->pegawai?->nama ?? 'Pegawai tidak ditemukan',
            'pj_initials' => $this->initials($dinas->pegawai?->nama ?? 'PJ'),
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

        return $parts->isNotEmpty() ? $parts->implode('') : 'PJ';
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
