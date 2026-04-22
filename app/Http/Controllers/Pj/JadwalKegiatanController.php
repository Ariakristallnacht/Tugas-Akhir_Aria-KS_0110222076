<?php

namespace App\Http\Controllers\Pj;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\PengajuanDinas;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalKegiatanController extends Controller
{
    private const DEFAULT_LAYANAN = [
        'PIPP & Skrining Kesehatan BPJS',
        'Kluster 2 Kesehatan Ibu',
        'Kluster 2 Balita & Anak',
        'Meja Tensi',
        'Kluster 3 Pelayanan Dewasa',
        'Layanan Lansia (>60 tahun)',
        'Kluster 2 & 3 TB (Tuberkulosis)',
        'Kluster 5 Pelayanan UGD',
        'Kluster 5 Pelayanan Laboratorium',
        'Apotek',
    ];

    private const EXCLUDED_LAYANAN = [
        'Pendaftaran',
        'Pelayanan Poli Umum Pagi',
        'Imunisasi Keliling Posyandu',
        'Penyuluhan Gizi Balita',
        'Kunjungan Rumah Lansia',
    ];

    public function index(Request $request): View
    {
        $calendarMonthInput = $request->string('calendar_month')->toString() ?: now()->format('Y-m');
        $monthDate = $this->parseMonth($calendarMonthInput);

        $referenceDate = now()->startOfDay();

        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->string('date_from'))->startOfDay()
            : now()->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->string('date_to'))->endOfDay()
            : now()->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $type = in_array($request->string('type')->toString(), ['all', 'layanan', 'dinas_luar'], true)
            ? $request->string('type')->toString()
            : 'all';

        $phase = in_array($request->string('phase')->toString(), ['all', 'upcoming', 'ongoing', 'completed'], true)
            ? $request->string('phase')->toString()
            : 'all';

        $jadwalItems = Jadwal::with(['kegiatan', 'pegawai'])
            ->whereBetween('tanggal', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->get()
            ->map(fn (Jadwal $jadwal) => $this->mapJadwal($jadwal, $referenceDate));

        $approvedDinasItems = PengajuanDinas::with('pegawai')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $dateTo->toDateString())
            ->whereDate('tanggal_selesai', '>=', $dateFrom->toDateString())
            ->orderBy('tanggal_mulai')
            ->get()
            ->map(fn (PengajuanDinas $pengajuan) => $this->mapPengajuanDinas($pengajuan, $referenceDate));

        $items = $jadwalItems
            ->concat($approvedDinasItems)
            ->when($type !== 'all', fn (Collection $collection) => $collection->where('type', $type))
            ->when($phase !== 'all', fn (Collection $collection) => $collection->where('phase', $phase))
            ->sortBy(fn (array $item) => sprintf('%02d-%010d-%s', $item['phase_order'], $item['start_sort'], $item['key']))
            ->values();

        $planningDate = $request->filled('planning_date')
            ? Carbon::parse($request->string('planning_date'))->startOfDay()
            : now()->startOfDay();

        $calendarQuery = $request->except('calendar_month');

        return view('pj.jadwal-kegiatan.index', [
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'planning_date' => $planningDate->toDateString(),
                'type' => $type,
                'phase' => $phase,
            ],
            'summary' => [
                'all' => $items->count(),
                'upcoming' => $items->where('phase', 'upcoming')->count(),
                'ongoing' => $items->where('phase', 'ongoing')->count(),
                'completed' => $items->where('phase', 'completed')->count(),
                'approved_dinas' => $approvedDinasItems->count(),
            ],
            'items' => $items,
            'calendarWeeks' => $this->buildCalendarWeeks($monthDate, $items, $referenceDate),
            'calendarMonthLabel' => $monthDate->translatedFormat('F Y'),
            'calendarFilters' => [
                'month' => $monthDate->format('Y-m'),
                'previous_month' => $monthDate->copy()->subMonth()->format('Y-m'),
                'next_month' => $monthDate->copy()->addMonth()->format('Y-m'),
                'current_month' => now()->format('Y-m'),
                'query' => $calendarQuery,
            ],
            'planningContext' => $this->buildPlanningContext($planningDate),
        ]);
    }

    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'ignore_jadwal' => ['nullable', 'integer', 'exists:jadwal,id'],
        ]);

        $ignoredJadwal = isset($validated['ignore_jadwal'])
            ? Jadwal::find($validated['ignore_jadwal'])
            : null;

        return response()->json(
            $this->buildPlanningContext(Carbon::parse($validated['date']), $ignoredJadwal)
        );
    }

    public function create(): View
    {
        $jadwal = new Jadwal([
            'tanggal' => now()->toDateString(),
            'status' => 'terjadwal',
        ]);

        return view('pj.jadwal-kegiatan.create', [
            'jadwal' => $jadwal,
            'planningContext' => $this->buildPlanningContext($jadwal->tanggal),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        DB::transaction(function () use ($validated) {
            $jadwal = Jadwal::create([
                'kegiatan_id' => $validated['kegiatan_id'],
                'created_by' => auth()->id(),
                'tanggal' => $validated['tanggal'],
                'waktu_mulai' => $validated['waktu_mulai'] ?? null,
                'waktu_selesai' => $validated['waktu_selesai'] ?? null,
                'lokasi' => $validated['lokasi'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $validated['status'],
            ]);

            $this->syncPetugas($jadwal, $validated['petugas']);
        });

        return redirect()
            ->route('pj.jadwal-kegiatan.index')
            ->with('success', 'Jadwal layanan berhasil disimpan tanpa bentrok penugasan pegawai.');
    }

    public function edit(Jadwal $jadwalKegiatan): View
    {
        $jadwalKegiatan->load('jadwalPegawai');

        return view('pj.jadwal-kegiatan.edit', [
            'jadwal' => $jadwalKegiatan,
            'planningContext' => $this->buildPlanningContext($jadwalKegiatan->tanggal, $jadwalKegiatan),
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Jadwal $jadwalKegiatan): RedirectResponse
    {
        $validated = $this->validateRequest($request, $jadwalKegiatan);

        DB::transaction(function () use ($jadwalKegiatan, $validated) {
            $jadwalKegiatan->update([
                'kegiatan_id' => $validated['kegiatan_id'],
                'tanggal' => $validated['tanggal'],
                'waktu_mulai' => $validated['waktu_mulai'] ?? null,
                'waktu_selesai' => $validated['waktu_selesai'] ?? null,
                'lokasi' => $validated['lokasi'],
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $validated['status'],
            ]);

            $this->syncPetugas($jadwalKegiatan, $validated['petugas']);
        });

        return redirect()
            ->route('pj.jadwal-kegiatan.index')
            ->with('success', 'Jadwal layanan berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwalKegiatan): RedirectResponse
    {
        try {
            $jadwalKegiatan->delete();
        } catch (QueryException) {
            return redirect()
                ->route('pj.jadwal-kegiatan.index')
                ->with('error', 'Jadwal kegiatan tidak dapat dihapus karena masih dipakai pada data lain.');
        }

        return redirect()
            ->route('pj.jadwal-kegiatan.index')
            ->with('success', 'Jadwal kegiatan berhasil dihapus.');
    }

    private function validateRequest(Request $request, ?Jadwal $jadwal = null): array
    {
        $validated = $request->validate([
            'kegiatan_id' => ['required', Rule::exists('kegiatan', 'id')->where(fn ($query) => $query->where('jenis', 'layanan')->where('is_aktif', true))],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['nullable', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i', 'after:waktu_mulai'],
            'lokasi' => ['required', 'string', 'max:200'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'terjadwal', 'berjalan', 'selesai', 'dibatalkan'])],
            'petugas' => ['required', 'array', 'min:1'],
            'petugas.*.pegawai_id' => ['required', 'distinct', Rule::exists('pegawai', 'id')->where(fn ($query) => $query->where('is_aktif', true))],
            'petugas.*.peran_tugas' => ['nullable', 'string', 'max:100'],
            'petugas.*.status_penugasan' => ['required', Rule::in(['dijadwalkan', 'hadir', 'izin', 'berhalangan'])],
        ]);

        $this->ensureNoAssignmentConflict($validated, $jadwal);

        return $validated;
    }

    private function ensureNoAssignmentConflict(array $validated, ?Jadwal $ignoredJadwal = null): void
    {
        $tanggal = Carbon::parse($validated['tanggal'])->toDateString();
        $pegawaiIds = collect($validated['petugas'])
            ->pluck('pegawai_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        if ($pegawaiIds->isEmpty()) {
            return;
        }

        $messages = [];
        $approvedDinasConflicts = PengajuanDinas::with('pegawai')
            ->where('status', 'disetujui')
            ->whereIn('pegawai_id', $pegawaiIds)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->get()
            ->keyBy('pegawai_id');

        foreach ($validated['petugas'] as $index => $petugas) {
            $pegawaiId = (int) ($petugas['pegawai_id'] ?? 0);
            $pengajuan = $approvedDinasConflicts->get($pegawaiId);

            if (! $pengajuan || ! $pengajuan->pegawai) {
                continue;
            }

            $messages["petugas.$index.pegawai_id"] = 'Pegawai '.$pengajuan->pegawai->nama
                .' sedang menjalankan dinas luar '
                .$pengajuan->kegiatan
                .' pada '.$pengajuan->tanggal_mulai->translatedFormat('d M Y')
                .' sampai '.$pengajuan->tanggal_selesai->translatedFormat('d M Y').'.';
        }

        $conflictingSchedules = Jadwal::with(['kegiatan', 'pegawai'])
            ->whereDate('tanggal', $tanggal)
            ->where('status', '!=', 'dibatalkan')
            ->when($ignoredJadwal, fn ($query) => $query->whereKeyNot($ignoredJadwal->id))
            ->whereHas('pegawai', fn ($query) => $query->whereIn('pegawai.id', $pegawaiIds))
            ->get();

        foreach ($validated['petugas'] as $index => $petugas) {
            $pegawaiId = (int) ($petugas['pegawai_id'] ?? 0);

            $scheduleConflict = $conflictingSchedules
                ->first(fn (Jadwal $jadwal) => $jadwal->pegawai->contains('id', $pegawaiId));

            if (! $scheduleConflict) {
                continue;
            }

            $messages["petugas.$index.pegawai_id"] = 'Pegawai '.$scheduleConflict->pegawai->firstWhere('id', $pegawaiId)?->nama
                .' sudah memiliki jadwal '
                .($scheduleConflict->kegiatan?->nama_kegiatan ?? 'kegiatan lain')
                .' pada '.$scheduleConflict->tanggal->translatedFormat('d M Y')
                .' pukul '.$this->formatTimeRange($scheduleConflict->waktu_mulai, $scheduleConflict->waktu_selesai).'.';
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function syncPetugas(Jadwal $jadwal, array $petugas): void
    {
        $payload = collect($petugas)
            ->mapWithKeys(fn (array $item) => [
                $item['pegawai_id'] => [
                    'peran_tugas' => $item['peran_tugas'] ?? null,
                    'status_penugasan' => $item['status_penugasan'],
                ],
            ])
            ->all();

        $jadwal->pegawai()->sync($payload);
    }

    private function formOptions(): array
    {
        $this->ensureDefaultLayanan();

        return [
            'kegiatanOptions' => Kegiatan::where('jenis', 'layanan')
                ->where('is_aktif', true)
                ->whereNotIn('nama_kegiatan', self::EXCLUDED_LAYANAN)
                ->get()
                ->sortBy(fn (Kegiatan $kegiatan) => $this->layananOrder($kegiatan->nama_kegiatan))
                ->values(),
            'pegawaiOptions' => Pegawai::where('is_aktif', true)->orderBy('nama')->get(),
            'statusOptions' => [
                'draft' => 'Draft',
                'terjadwal' => 'Terjadwal',
                'berjalan' => 'Berjalan',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
            ],
            'statusPenugasanOptions' => [
                'dijadwalkan' => 'Dijadwalkan',
                'hadir' => 'Hadir',
                'izin' => 'Izin',
                'berhalangan' => 'Berhalangan',
            ],
        ];
    }

    private function buildPlanningContext(Carbon|string|null $date, ?Jadwal $ignoredJadwal = null): array
    {
        $selectedDate = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date ?: now())->startOfDay();

        $approvedDinas = PengajuanDinas::with('pegawai')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $selectedDate->toDateString())
            ->whereDate('tanggal_selesai', '>=', $selectedDate->toDateString())
            ->orderBy('tanggal_mulai')
            ->get();

        $existingSchedules = Jadwal::with(['kegiatan', 'pegawai'])
            ->whereDate('tanggal', $selectedDate->toDateString())
            ->where('status', '!=', 'dibatalkan')
            ->when($ignoredJadwal, fn ($query) => $query->whereKeyNot($ignoredJadwal->id))
            ->orderBy('waktu_mulai')
            ->get();

        $unavailableByPegawai = [];

        foreach ($approvedDinas as $pengajuan) {
            if (! $pengajuan->pegawai) {
                continue;
            }

            $pegawaiId = $pengajuan->pegawai->id;
            $unavailableByPegawai[$pegawaiId]['name'] = $pengajuan->pegawai->nama;
            $unavailableByPegawai[$pegawaiId]['jabatan'] = $pengajuan->pegawai->jabatan;
            $unavailableByPegawai[$pegawaiId]['reasons'][] = [
                'label' => 'Dinas luar: '.$pengajuan->kegiatan,
                'detail' => $pengajuan->tujuan.' ('.$pengajuan->tanggal_mulai->translatedFormat('d M Y').' - '.$pengajuan->tanggal_selesai->translatedFormat('d M Y').')',
            ];
        }

        foreach ($existingSchedules as $jadwal) {
            foreach ($jadwal->pegawai as $pegawai) {
                $pegawaiId = $pegawai->id;
                $unavailableByPegawai[$pegawaiId]['name'] = $pegawai->nama;
                $unavailableByPegawai[$pegawaiId]['jabatan'] = $pegawai->jabatan;
                $unavailableByPegawai[$pegawaiId]['reasons'][] = [
                    'label' => 'Sudah ada jadwal layanan',
                    'detail' => ($jadwal->kegiatan?->nama_kegiatan ?? 'Kegiatan')
                        .' • '.$this->formatTimeRange($jadwal->waktu_mulai, $jadwal->waktu_selesai)
                        .' • '.$jadwal->lokasi,
                ];
            }
        }

        $availabilityMap = collect($unavailableByPegawai)
            ->mapWithKeys(function (array $item, int $pegawaiId) use ($selectedDate) {
                $summary = collect($item['reasons'] ?? [])
                    ->pluck('label')
                    ->implode('; ');

                $details = collect($item['reasons'] ?? [])
                    ->pluck('detail')
                    ->filter()
                    ->values()
                    ->all();

                return [
                    $pegawaiId => [
                        'name' => $item['name'] ?? 'Pegawai',
                        'jabatan' => $item['jabatan'] ?? '-',
                        'summary' => $summary,
                        'details' => $details,
                        'date_label' => $selectedDate->translatedFormat('d F Y'),
                    ],
                ];
            })
            ->all();

        $activePegawaiCount = Pegawai::where('is_aktif', true)->count();

        return [
            'selected_date' => $selectedDate->toDateString(),
            'selected_date_label' => $selectedDate->translatedFormat('d F Y'),
            'approved_dinas' => $approvedDinas->map(fn (PengajuanDinas $pengajuan) => [
                'pegawai' => $pengajuan->pegawai?->nama ?? 'Pegawai',
                'jabatan' => $pengajuan->pegawai?->jabatan ?? '-',
                'kegiatan' => $pengajuan->kegiatan,
                'tujuan' => $pengajuan->tujuan,
                'range_label' => $pengajuan->tanggal_mulai->isSameDay($pengajuan->tanggal_selesai)
                    ? $pengajuan->tanggal_mulai->translatedFormat('d M Y')
                    : $pengajuan->tanggal_mulai->translatedFormat('d M Y').' - '.$pengajuan->tanggal_selesai->translatedFormat('d M Y'),
            ])->values()->all(),
            'existing_schedules' => $existingSchedules->map(fn (Jadwal $jadwal) => [
                'title' => $jadwal->kegiatan?->nama_kegiatan ?? 'Kegiatan',
                'time_label' => $this->formatTimeRange($jadwal->waktu_mulai, $jadwal->waktu_selesai),
                'lokasi' => $jadwal->lokasi,
                'pegawai' => $jadwal->pegawai->pluck('nama')->implode(', ') ?: 'Belum ada petugas',
            ])->values()->all(),
            'availability_summary' => [
                'available_count' => max($activePegawaiCount - count($availabilityMap), 0),
                'unavailable_count' => count($availabilityMap),
                'active_count' => $activePegawaiCount,
            ],
            'availability_map' => $availabilityMap,
        ];
    }

    private function ensureDefaultLayanan(): void
    {
        foreach (self::DEFAULT_LAYANAN as $namaLayanan) {
            Kegiatan::firstOrCreate(
                ['nama_kegiatan' => $namaLayanan],
                [
                    'jenis' => 'layanan',
                    'deskripsi' => str_contains(mb_strtolower($namaLayanan), 'kluster')
                        ? 'Poli layanan Puskesmas Bunar.'
                        : 'Layanan penunjang Puskesmas Bunar.',
                    'is_aktif' => true,
                ]
            );
        }
    }

    private function layananOrder(string $namaKegiatan): int
    {
        $position = array_search($namaKegiatan, self::DEFAULT_LAYANAN, true);

        return $position === false ? 999 : $position;
    }

    private function parseMonth(string $value): Carbon
    {
        return preg_match('/^\d{4}-\d{2}$/', $value) === 1
            ? Carbon::createFromFormat('Y-m', $value)->startOfMonth()
            : now()->startOfMonth();
    }

    private function mapJadwal(Jadwal $jadwal, Carbon $referenceDate): array
    {
        $startDate = $jadwal->tanggal->copy()->startOfDay();
        $endDate = $jadwal->tanggal->copy()->endOfDay();
        $phase = $this->determinePhase($startDate, $endDate, $referenceDate);
        $pegawaiNames = $jadwal->pegawai->pluck('nama');
        $displayNames = $pegawaiNames->take(3)->implode(', ');
        $firstName = $pegawaiNames->first() ?: 'PJ';

        return [
            'key' => 'jadwal-'.$jadwal->id,
            'id' => $jadwal->id,
            'type' => 'layanan',
            'type_label' => 'Jadwal Layanan',
            'title' => $jadwal->kegiatan?->nama_kegiatan ?? 'Kegiatan',
            'subtitle' => $jadwal->lokasi,
            'description' => $jadwal->keterangan,
            'people' => $displayNames ?: 'Belum ada petugas',
            'pj_initials' => $this->initials($firstName),
            'date_label' => $startDate->translatedFormat('d M Y'),
            'time_label' => $this->formatTimeRange($jadwal->waktu_mulai, $jadwal->waktu_selesai),
            'phase' => $phase,
            'phase_label' => $this->phaseLabel($phase),
            'phase_order' => $this->phaseOrder($phase),
            'meta_status' => ucfirst($jadwal->status),
            'start_date' => $startDate->copy(),
            'end_date' => $endDate->copy(),
            'start_sort' => $startDate->copy()->setTimeFromTimeString($jadwal->waktu_mulai?->format('H:i:s') ?? '00:00:00')->timestamp,
            'edit_url' => route('pj.jadwal-kegiatan.edit', $jadwal),
            'delete_url' => route('pj.jadwal-kegiatan.destroy', $jadwal),
            'reference_note' => null,
        ];
    }

    private function mapPengajuanDinas(PengajuanDinas $pengajuan, Carbon $referenceDate): array
    {
        $startDate = $pengajuan->tanggal_mulai->copy()->startOfDay();
        $endDate = $pengajuan->tanggal_selesai->copy()->endOfDay();
        $phase = $this->determinePhase($startDate, $endDate, $referenceDate);

        return [
            'key' => 'dinas-'.$pengajuan->id,
            'id' => $pengajuan->id,
            'type' => 'dinas_luar',
            'type_label' => 'Dinas Luar Disetujui',
            'title' => $pengajuan->kegiatan,
            'subtitle' => $pengajuan->tujuan,
            'description' => $pengajuan->keterangan,
            'people' => $pengajuan->pegawai?->nama ?? 'Pegawai',
            'pj_initials' => $this->initials($pengajuan->pegawai?->nama ?? 'DL'),
            'date_label' => $startDate->isSameDay($endDate)
                ? $startDate->translatedFormat('d M Y')
                : $startDate->translatedFormat('d M Y').' - '.$endDate->translatedFormat('d M Y'),
            'time_label' => 'Dinas luar penuh hari',
            'phase' => $phase,
            'phase_label' => $this->phaseLabel($phase),
            'phase_order' => $this->phaseOrder($phase),
            'meta_status' => 'Disetujui',
            'start_date' => $startDate->copy(),
            'end_date' => $endDate->copy(),
            'start_sort' => $startDate->timestamp,
            'edit_url' => null,
            'delete_url' => null,
            'reference_note' => 'Referensi dinas luar yang perlu diperhitungkan saat menyusun layanan poli.',
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
