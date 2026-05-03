<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\JadwalPegawai;
use App\Models\Kegiatan;
use App\Models\LaporanKegiatan;
use App\Models\Monitoring;
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
            'nama_kegiatan' => 'Skrining Kesehatan BPJS / CKG',
            'deskripsi' => 'Skrining faktor risiko penyakit tidak menular dan pemeriksaan kesehatan dasar peserta BPJS.',
            'lokasi' => 'Lobby Pendaftaran',
            'ringkasan' => 'Skrining tekanan darah, gula darah sewaktu, dan konseling faktor risiko.',
        ],
        [
            'nama_kegiatan' => 'PIPP',
            'deskripsi' => 'Pemberian informasi, penanganan pengaduan, dan pelayanan pelanggan puskesmas.',
            'lokasi' => 'Meja Informasi Pelayanan',
            'ringkasan' => 'Pendampingan alur layanan dan tindak lanjut keluhan pasien.',
        ],
        [
            'nama_kegiatan' => 'Kluster 2 Kesehatan Ibu',
            'deskripsi' => 'Pemeriksaan antenatal, konseling ibu hamil, dan pemantauan risiko kehamilan.',
            'lokasi' => 'Ruang KIA',
            'ringkasan' => 'Pelayanan ANC terpadu dan konseling persiapan persalinan.',
        ],
        [
            'nama_kegiatan' => 'Kluster 2 Balita dan Anak',
            'deskripsi' => 'Pemantauan tumbuh kembang, imunisasi, dan edukasi kesehatan balita.',
            'lokasi' => 'Ruang Balita',
            'ringkasan' => 'Imunisasi dasar, penimbangan, dan edukasi pengasuhan.',
        ],
        [
            'nama_kegiatan' => 'Meja Tensi dan Triage',
            'deskripsi' => 'Pemeriksaan tanda vital awal sebelum pasien masuk ke poli tujuan.',
            'lokasi' => 'Area Triage',
            'ringkasan' => 'Pemeriksaan tanda vital awal untuk seluruh pasien rawat jalan.',
        ],
        [
            'nama_kegiatan' => 'Kluster 3 Pelayanan Dewasa',
            'deskripsi' => 'Pelayanan pemeriksaan umum pasien dewasa dengan kasus rawat jalan.',
            'lokasi' => 'Poli Dewasa',
            'ringkasan' => 'Pelayanan pasien hipertensi, ISPA, diabetes, dan kasus umum lainnya.',
        ],
        [
            'nama_kegiatan' => 'Kluster 3 Layanan Lansia',
            'deskripsi' => 'Pelayanan kesehatan lansia mencakup skrining geriatri dan pemantauan penyakit kronis.',
            'lokasi' => 'Poli Lansia',
            'ringkasan' => 'Skrining geriatri dasar dan evaluasi kepatuhan pengobatan.',
        ],
        [
            'nama_kegiatan' => 'Pelayanan TB Terpadu',
            'deskripsi' => 'Penemuan kasus, pemantauan terapi, dan edukasi pasien tuberkulosis.',
            'lokasi' => 'Klinik TB',
            'ringkasan' => 'Pemantauan pengobatan TB dan edukasi kepatuhan minum obat.',
        ],
        [
            'nama_kegiatan' => 'Pelayanan UGD',
            'deskripsi' => 'Pelayanan kegawatdaruratan dasar dan stabilisasi pasien sebelum rujukan.',
            'lokasi' => 'UGD',
            'ringkasan' => 'Stabilisasi awal pasien gawat darurat sebelum tindak lanjut.',
        ],
        [
            'nama_kegiatan' => 'Pelayanan Laboratorium',
            'deskripsi' => 'Pemeriksaan laboratorium dasar untuk mendukung diagnosis dan tindak lanjut layanan.',
            'lokasi' => 'Laboratorium',
            'ringkasan' => 'Pemeriksaan hematologi sederhana dan kimia klinik dasar.',
        ],
        [
            'nama_kegiatan' => 'Pelayanan Farmasi',
            'deskripsi' => 'Dispensing obat, edukasi penggunaan obat, dan monitoring ketersediaan farmasi.',
            'lokasi' => 'Apotek',
            'ringkasan' => 'Penyerahan obat dan edukasi aturan pakai kepada pasien.',
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

            $adminVerifier = User::query()
                ->whereHas('role', fn ($query) => $query->where('kode', 'admin'))
                ->first();

            if (! $creator || ! $adminVerifier) {
                throw new RuntimeException('User admin atau pj_penjadwalan tidak ditemukan.');
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
                        '%s untuk tanggal %s di wilayah kerja Puskesmas Bunar.',
                        $service['ringkasan'],
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

                    Monitoring::query()->create([
                        'jadwal_id' => $jadwal->id,
                        'pegawai_id' => $pegawaiItem->id,
                        'status' => 'selesai',
                        'laporan' => sprintf(
                            'Monitoring %s pada layanan %s menunjukkan pelaksanaan sesuai SOP dan jadwal shift.',
                            $pegawaiItem->nama,
                            $service['nama_kegiatan']
                        ),
                        'dipantau_at' => $dayCursor->copy()->setTime(11, 45),
                    ]);
                }

                LaporanKegiatan::query()->create([
                    'jadwal_id' => $jadwal->id,
                    'pegawai_id' => $assignedPegawai[0]->id,
                    'tanggal' => $dayCursor->toDateString(),
                    'laporan' => sprintf(
                        'Pelayanan %s tanggal %s selesai dilaksanakan dengan alur pasien tertib, dokumentasi lengkap, dan kebutuhan logistik terpenuhi.',
                        $service['nama_kegiatan'],
                        $dayCursor->translatedFormat('d F Y')
                    ),
                    'status_verifikasi' => 'diterima',
                    'diverifikasi_oleh' => $adminVerifier->id,
                    'diverifikasi_at' => $dayCursor->copy()->setTime(16, 30),
                    'catatan_verifikasi' => 'Laporan historis lengkap dan sesuai data monitoring lapangan.',
                ]);
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
