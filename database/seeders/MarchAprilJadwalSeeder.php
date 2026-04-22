<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\JadwalPegawai;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MarchAprilJadwalSeeder extends Seeder
{
    private const START_DATE = '2026-03-01';

    private const END_DATE = '2026-04-30';

    private const LAYANAN = [
        [
            'nama_kegiatan' => 'PIPP & Skrining Kesehatan BPJS',
            'deskripsi' => 'Layanan pendaftaran BPJS dan skrining kesehatan awal.',
            'lokasi' => 'Lobby Pendaftaran',
        ],
        [
            'nama_kegiatan' => 'Kluster 2 Kesehatan Ibu',
            'deskripsi' => 'Poli layanan kesehatan ibu.',
            'lokasi' => 'Ruang KIA',
        ],
        [
            'nama_kegiatan' => 'Kluster 2 Balita & Anak',
            'deskripsi' => 'Poli layanan balita dan anak.',
            'lokasi' => 'Ruang Balita',
        ],
        [
            'nama_kegiatan' => 'Meja Tensi',
            'deskripsi' => 'Layanan pemeriksaan tanda vital awal.',
            'lokasi' => 'Area Triage',
        ],
        [
            'nama_kegiatan' => 'Kluster 3 Pelayanan Dewasa',
            'deskripsi' => 'Poli layanan dewasa.',
            'lokasi' => 'Poli Dewasa',
        ],
        [
            'nama_kegiatan' => 'Layanan Lansia (>60 tahun)',
            'deskripsi' => 'Layanan khusus pasien lansia usia lebih dari 60 tahun.',
            'lokasi' => 'Poli Lansia',
        ],
        [
            'nama_kegiatan' => 'Kluster 2 & 3 TB (Tuberkulosis)',
            'deskripsi' => 'Poli layanan tuberkulosis.',
            'lokasi' => 'Klinik TB',
        ],
        [
            'nama_kegiatan' => 'Kluster 5 Pelayanan UGD',
            'deskripsi' => 'Poli layanan unit gawat darurat.',
            'lokasi' => 'UGD',
        ],
        [
            'nama_kegiatan' => 'Kluster 5 Pelayanan Laboratorium',
            'deskripsi' => 'Poli layanan laboratorium.',
            'lokasi' => 'Laboratorium',
        ],
        [
            'nama_kegiatan' => 'Apotek',
            'deskripsi' => 'Layanan farmasi dan pengambilan obat.',
            'lokasi' => 'Apotek',
        ],
    ];

    private const SHIFT_TEMPLATES = [
        ['mulai' => '07:30:00', 'selesai' => '10:30:00'],
        ['mulai' => '08:00:00', 'selesai' => '11:00:00'],
        ['mulai' => '08:30:00', 'selesai' => '11:30:00'],
        ['mulai' => '09:00:00', 'selesai' => '12:00:00'],
    ];

    private const ROLE_TEMPLATES = [
        'Koordinator Layanan',
        'Petugas Pemeriksaan',
        'Petugas Administrasi',
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $creator = User::query()
                ->whereHas('role', fn ($query) => $query->where('kode', 'pj_penjadwalan'))
                ->first();

            if (! $creator) {
                throw new RuntimeException('User dengan role pj_penjadwalan tidak ditemukan.');
            }

            $pegawai = Pegawai::query()
                ->where('is_aktif', true)
                ->whereHas('user.role', fn ($query) => $query->whereIn('kode', ['pj_penjadwalan', 'pegawai']))
                ->orderBy('nama')
                ->get()
                ->values();

            if ($pegawai->count() < 3) {
                throw new RuntimeException('Seeder membutuhkan minimal 3 pegawai aktif.');
            }

            $kegiatanByName = collect(self::LAYANAN)
                ->mapWithKeys(function (array $item) {
                    $kegiatan = Kegiatan::query()->firstOrCreate(
                        ['nama_kegiatan' => $item['nama_kegiatan']],
                        [
                            'jenis' => 'layanan',
                            'deskripsi' => $item['deskripsi'],
                            'is_aktif' => true,
                        ]
                    );

                    if (! $kegiatan->is_aktif || $kegiatan->jenis !== 'layanan') {
                        $kegiatan->update([
                            'jenis' => 'layanan',
                            'deskripsi' => $item['deskripsi'],
                            'is_aktif' => true,
                        ]);
                    }

                    return [$item['nama_kegiatan'] => $kegiatan];
                });

            $startDate = Carbon::createFromFormat('Y-m-d', self::START_DATE)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', self::END_DATE)->startOfDay();

            Jadwal::query()
                ->whereDate('tanggal', '>=', $startDate->toDateString())
                ->whereDate('tanggal', '<=', $endDate->toDateString())
                ->delete();

            $serviceTemplates = array_values(self::LAYANAN);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            for ($dayIndex = 0; $dayIndex < $totalDays; $dayIndex++) {
                $dayCursor = $startDate->copy()->addDays($dayIndex);
                $service = $serviceTemplates[$dayIndex % count($serviceTemplates)];
                $shift = self::SHIFT_TEMPLATES[$dayIndex % count(self::SHIFT_TEMPLATES)];
                $assignedPegawai = $this->pickPegawai($pegawai->all(), $dayIndex, 3);

                $jadwal = Jadwal::query()->create([
                    'kegiatan_id' => $kegiatanByName[$service['nama_kegiatan']]->id,
                    'created_by' => $creator->id,
                    'tanggal' => $dayCursor->toDateString(),
                    'waktu_mulai' => $shift['mulai'],
                    'waktu_selesai' => $shift['selesai'],
                    'lokasi' => $service['lokasi'],
                    'keterangan' => sprintf(
                        'Jadwal layanan %s untuk tanggal %s.',
                        $service['nama_kegiatan'],
                        $dayCursor->translatedFormat('d F Y')
                    ),
                    'status' => 'selesai',
                ]);

                foreach ($assignedPegawai as $index => $pegawaiItem) {
                    JadwalPegawai::query()->create([
                        'jadwal_id' => $jadwal->id,
                        'pegawai_id' => $pegawaiItem->id,
                        'peran_tugas' => self::ROLE_TEMPLATES[$index] ?? 'Petugas Layanan',
                        'status_penugasan' => 'hadir',
                    ]);
                }
            }
        });
    }

    /**
     * @param  array<int, \App\Models\Pegawai>  $pegawai
     * @return array<int, \App\Models\Pegawai>
     */
    private function pickPegawai(array $pegawai, int $offset, int $count): array
    {
        $selected = [];
        $pegawaiCount = count($pegawai);

        for ($index = 0; $index < $count; $index++) {
            $selected[] = $pegawai[($offset + $index) % $pegawaiCount];
        }

        return $selected;
    }
}
