<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\JadwalPegawai;
use App\Models\Kegiatan;
use App\Models\LaporanKegiatan;
use App\Models\Monitoring;
use App\Models\Pegawai;
use App\Models\PengajuanDinas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JulyAugustJadwalSeeder extends Seeder
{
    private const LAYANAN = [
        [
            'nama_kegiatan' => 'Pendaftaran dan Rekam Medis',
            'deskripsi' => 'Pelayanan registrasi pasien, verifikasi kepesertaan, dan pencatatan rekam medis harian.',
            'lokasi' => 'Loket Pendaftaran',
            'ringkasan' => 'Pelayanan registrasi dan pengelolaan rekam medis.',
        ],
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

    private const DINAS_LUAR = [
        [
            'nama_kegiatan' => 'Supervisi Sanitasi Sekolah',
            'deskripsi' => 'Pemantauan sarana sanitasi, air bersih, dan kebersihan lingkungan sekolah di wilayah kerja.',
            'tujuan' => 'Sekolah wilayah Bunar',
            'keterangan' => 'Kunjungan lapangan untuk supervisi sanitasi dan pendampingan tindak lanjut perbaikan.',
        ],
        [
            'nama_kegiatan' => 'Investigasi Epidemiologi DBD',
            'deskripsi' => 'Pelacakan kasus demam berdarah, asesmen lingkungan, dan koordinasi pemberantasan sarang nyamuk.',
            'tujuan' => 'Wilayah binaan Bunar',
            'keterangan' => 'Koordinasi lapangan untuk investigasi kasus dan edukasi PSN bersama kader.',
        ],
        [
            'nama_kegiatan' => 'Pembinaan Posyandu Remaja',
            'deskripsi' => 'Pendampingan kader dan edukasi kesehatan reproduksi pada kelompok remaja.',
            'tujuan' => 'Posyandu Remaja Bunar',
            'keterangan' => 'Pendampingan kegiatan posyandu remaja dan penguatan edukasi kesehatan.',
        ],
    ];

    private const ROLE_TEMPLATES = [
        'Koordinator Layanan',
        'Petugas Pemeriksaan',
        'Petugas Administrasi',
        'Petugas Pelaksana',
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

            // Get all active pegawai with role pegawai or pj_penjadwalan
            $pegawaiList = Pegawai::query()
                ->where('is_aktif', true)
                ->whereHas('user.role', fn ($query) => $query->whereIn('kode', ['pj_penjadwalan', 'pegawai']))
                ->orderBy('nama')
                ->get()
                ->values();

            $pegawaiCount = $pegawaiList->count();
            if ($pegawaiCount === 0) {
                throw new RuntimeException('Tidak ada pegawai aktif untuk dijadwalkan.');
            }

            // Sync Kegiatan
            $kegiatanByName = collect(self::LAYANAN)->mapWithKeys(function (array $item) {
                $kegiatan = Kegiatan::query()->updateOrCreate(
                    ['nama_kegiatan' => $item['nama_kegiatan']],
                    [
                        'jenis' => 'layanan',
                        'deskripsi' => $item['deskripsi'],
                        'is_aktif' => true,
                    ]
                );
                return [$item['nama_kegiatan'] => $kegiatan];
            });

            collect(self::DINAS_LUAR)->each(function (array $item) {
                Kegiatan::query()->updateOrCreate(
                    ['nama_kegiatan' => $item['nama_kegiatan']],
                    [
                        'jenis' => 'dinas_luar',
                        'deskripsi' => $item['deskripsi'],
                        'is_aktif' => true,
                    ]
                );
            });

            // Date Range: July 1 to August 31, 2026
            $startDate = Carbon::create(2026, 7, 1)->startOfDay();
            $endDate = Carbon::create(2026, 8, 31)->endOfDay();

            // Cleanup existing records in range for idempotency
            $dateStrings = [];
            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $dateStrings[] = $cursor->toDateString();
                $cursor->addDay();
            }

            LaporanKegiatan::query()->whereIn('tanggal', $dateStrings)->delete();
            Monitoring::query()->whereHas('jadwal', fn ($q) => $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]))->delete();
            JadwalPegawai::query()->whereHas('jadwal', fn ($q) => $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]))->delete();
            Jadwal::query()->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])->delete();
            PengajuanDinas::query()
                ->where(fn ($q) => $q->whereBetween('tanggal_mulai', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('tanggal_selesai', [$startDate->toDateString(), $endDate->toDateString()]))
                ->delete();

            // Run schedule distribution day by day
            $currentDate = $startDate->copy();
            $dayIndex = 0;

            while ($currentDate->lte($endDate)) {
                if ($currentDate->isSunday()) {
                    $currentDate->addDay();
                    continue;
                }

                // Working Hours
                // Mon-Fri: 08:00 to 16:00
                // Sat: 08:00 to 14:00
                $startTime = '08:00:00';
                $endTime = $currentDate->isSaturday() ? '14:00:00' : '16:00:00';

                // Rotate employee list to distribute assignments fairly each day
                $rotatedPegawai = $this->rotatePegawai($pegawaiList, $dayIndex);

                // Split employees: ~33% go to Dinas Luar, ~67% go to Layanan
                $numDinasLuar = max(1, (int) ($pegawaiCount / 3));
                $dinasEmployees = $rotatedPegawai->take($numDinasLuar)->values();
                $layananEmployees = $rotatedPegawai->skip($numDinasLuar)->values();

                // 1. Seed Dinas Luar for assigned employees
                foreach ($dinasEmployees as $empIndex => $employee) {
                    $dinasTemplate = self::DINAS_LUAR[($dayIndex + $empIndex) % count(self::DINAS_LUAR)];
                    $isFuture = $currentDate->isFuture();
                    $status = 'disetujui';

                    $pengajuan = PengajuanDinas::query()->create([
                        'pegawai_id' => $employee->id,
                        'tanggal_pengajuan' => $currentDate->copy()->subDays(2)->toDateString(),
                        'tanggal_mulai' => $currentDate->toDateString(),
                        'tanggal_selesai' => $currentDate->toDateString(),
                        'tujuan' => $dinasTemplate['tujuan'],
                        'kegiatan' => $dinasTemplate['nama_kegiatan'],
                        'keterangan' => sprintf(
                            '%s untuk tanggal %s di wilayah kerja Puskesmas Bunar.',
                            $dinasTemplate['keterangan'],
                            $currentDate->translatedFormat('d F Y')
                        ),
                        'bukti_surat_path' => null,
                        'bukti_surat_nama' => null,
                        'bukti_surat_mime' => null,
                        'status' => $status,
                        'diverifikasi_oleh' => $adminVerifier->id,
                        'diverifikasi_at' => $currentDate->copy()->setTime(8, 0),
                        'catatan_verifikasi' => 'Dinas luar disetujui untuk penugasan harian.',
                    ]);

                    if (! $isFuture) {
                        LaporanKegiatan::query()->create([
                            'jenis_kegiatan' => 'dinas_luar',
                            'pegawai_id' => $employee->id,
                            'pengajuan_dinas_id' => $pengajuan->id,
                            'tanggal' => $currentDate->toDateString(),
                            'laporan' => sprintf(
                                'Pelaksanaan dinas luar %s tanggal %s berjalan lancar dan terdokumentasi dengan baik.',
                                $dinasTemplate['nama_kegiatan'],
                                $currentDate->translatedFormat('d F Y')
                            ),
                            'status_verifikasi' => 'diterima',
                            'diverifikasi_oleh' => $adminVerifier->id,
                            'diverifikasi_at' => $currentDate->copy()->setTime(16, 0),
                            'catatan_verifikasi' => 'Laporan dinas luar sesuai dengan tugas yang diberikan.',
                        ]);
                    }
                }

                // 2. Seed Layanan for assigned employees
                foreach ($layananEmployees as $empIndex => $employee) {
                    $layananTemplate = self::LAYANAN[($dayIndex + $empIndex) % count(self::LAYANAN)];
                    $isFuture = $currentDate->isFuture();
                    $status = $isFuture ? 'terjadwal' : 'selesai';

                    $jadwal = Jadwal::query()->create([
                        'kegiatan_id' => $kegiatanByName[$layananTemplate['nama_kegiatan']]->id,
                        'created_by' => $creator->id,
                        'tanggal' => $currentDate->toDateString(),
                        'waktu_mulai' => $startTime,
                        'waktu_selesai' => $endTime,
                        'lokasi' => $layananTemplate['lokasi'],
                        'keterangan' => sprintf(
                            '%s untuk tanggal %s di wilayah kerja Puskesmas Bunar.',
                            $layananTemplate['ringkasan'],
                            $currentDate->translatedFormat('d F Y')
                        ),
                        'status' => $status,
                    ]);

                    JadwalPegawai::query()->create([
                        'jadwal_id' => $jadwal->id,
                        'pegawai_id' => $employee->id,
                        'peran_tugas' => self::ROLE_TEMPLATES[$empIndex % count(self::ROLE_TEMPLATES)],
                        'status_penugasan' => $status === 'selesai' ? 'hadir' : 'dijadwalkan',
                    ]);

                    Monitoring::query()->create([
                        'jadwal_id' => $jadwal->id,
                        'pegawai_id' => $employee->id,
                        'status' => $status === 'selesai' ? 'selesai' : 'belum_mulai',
                        'laporan' => $status === 'selesai'
                            ? sprintf(
                                'Monitoring %s pada layanan %s menunjukkan pelaksanaan sesuai SOP dan jadwal shift.',
                                $employee->nama,
                                $layananTemplate['nama_kegiatan']
                            )
                            : null,
                        'dipantau_at' => $status === 'selesai'
                            ? $currentDate->copy()->setTime(11, 45)
                            : null,
                    ]);

                    if (! $isFuture) {
                        LaporanKegiatan::query()->create([
                            'jenis_kegiatan' => 'layanan',
                            'jadwal_id' => $jadwal->id,
                            'pegawai_id' => $employee->id,
                            'tanggal' => $currentDate->toDateString(),
                            'laporan' => sprintf(
                                'Pelayanan %s tanggal %s selesai dilaksanakan dengan alur pasien tertib, dokumentasi lengkap, dan kebutuhan logistik terpenuhi.',
                                $layananTemplate['nama_kegiatan'],
                                $currentDate->translatedFormat('d F Y')
                            ),
                            'status_verifikasi' => 'diterima',
                            'diverifikasi_oleh' => $adminVerifier->id,
                            'diverifikasi_at' => $currentDate->copy()->setTime(16, 30),
                            'catatan_verifikasi' => 'Laporan sesuai data monitoring lapangan.',
                        ]);
                    }
                }

                $dayIndex++;
                $currentDate->addDay();
            }
        });
    }

    private function rotatePegawai($pegawai, int $offset)
    {
        if ($pegawai->isEmpty()) {
            return $pegawai;
        }

        $rotation = $offset % $pegawai->count();

        return $pegawai->slice($rotation)
            ->concat($pegawai->take($rotation))
            ->values();
    }
}
