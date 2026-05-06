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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $this->resetApplicationTables();

        DB::transaction(function () {
            $roleIds = $this->resolveRoleIds();
            $pegawaiRecords = $this->seedPegawaiAndUsers($roleIds);
            $kegiatanByName = $this->seedKegiatan();

            $usersByRole = $pegawaiRecords->mapWithKeys(
                fn (array $item) => [$item['role_kode'].'_'.$item['pegawai']->nama => $item['user']]
            );
            $pegawaiByName = $pegawaiRecords->mapWithKeys(
                fn (array $item) => [$item['pegawai']->nama => $item['pegawai']]
            );

            $jadwalList = $this->seedOperationalJadwal($kegiatanByName, $pegawaiByName, $usersByRole);
            $this->seedPengajuanDinas($pegawaiByName, $usersByRole);
            $this->seedMonitoringAndReports($jadwalList, $usersByRole);
        });

        $this->call(DefaultRoleAccountSeeder::class);

        $this->call(MarchAprilJadwalSeeder::class);
    }

    /**
     * @return array<string, int>
     */
    private function resolveRoleIds(): array
    {
        return [
            'admin' => DB::table('roles')->where('kode', 'admin')->value('id'),
            'pj_penjadwalan' => DB::table('roles')->where('kode', 'pj_penjadwalan')->value('id'),
            'pegawai' => DB::table('roles')->where('kode', 'pegawai')->value('id'),
        ];
    }

    /**
     * @param  array<string, int>  $roleIds
     * @return \Illuminate\Support\Collection<int, array{pegawai: \App\Models\Pegawai, user: \App\Models\User, role_kode: string}>
     */
    private function seedPegawaiAndUsers(array $roleIds): Collection
    {
        return collect([
            [
                'nip' => '197812142005012001',
                'nama' => 'Nur Aisyah Pratama',
                'jabatan' => 'Kepala Tata Usaha',
                'unit_kerja' => 'Tata Usaha',
                'no_hp' => '081210450101',
                'alamat' => 'Jl. Raya Bunar No. 12, Balaraja, Tangerang',
                'is_aktif' => true,
                'email' => 'admin@puskesmasbunar.id',
                'role_kode' => 'admin',
            ],
            [
                'nip' => '198604222010012002',
                'nama' => 'Rina Fitriani',
                'jabatan' => 'Koordinator Penjadwalan Pelayanan',
                'unit_kerja' => 'Pelayanan Kesehatan',
                'no_hp' => '081210450102',
                'alamat' => 'Perumahan Bunar Sejahtera Blok C7, Tangerang',
                'is_aktif' => true,
                'email' => 'rina.fitriani@puskesmasbunar.id',
                'role_kode' => 'pj_penjadwalan',
            ],
            [
                'nip' => '199103102015022003',
                'nama' => 'Dewi Lestari',
                'jabatan' => 'Perawat Pelaksana',
                'unit_kerja' => 'Poli Umum',
                'no_hp' => '081210450103',
                'alamat' => 'Kp. Bunar Tengah RT 03/RW 02, Tangerang',
                'is_aktif' => true,
                'email' => 'dewi.lestari@puskesmasbunar.id',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '198909172014022004',
                'nama' => 'Siska Handayani',
                'jabatan' => 'Bidan Koordinator',
                'unit_kerja' => 'KIA dan KB',
                'no_hp' => '081210450104',
                'alamat' => 'Jl. Melati Indah No. 8, Bunar, Tangerang',
                'is_aktif' => true,
                'email' => 'siska.handayani@puskesmasbunar.id',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '199208082016032005',
                'nama' => 'Andi Saputra',
                'jabatan' => 'Petugas Promosi Kesehatan',
                'unit_kerja' => 'Promkes',
                'no_hp' => '081210450105',
                'alamat' => 'Griya Bunar Asri Blok A5, Tangerang',
                'is_aktif' => true,
                'email' => 'andi.saputra@puskesmasbunar.id',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '199406112017042006',
                'nama' => 'Maya Puspitasari',
                'jabatan' => 'Nutrisionis',
                'unit_kerja' => 'Gizi',
                'no_hp' => '081210450106',
                'alamat' => 'Komplek Bhakti Medika No. 4, Tangerang',
                'is_aktif' => true,
                'email' => 'maya.puspitasari@puskesmasbunar.id',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '198711252012012007',
                'nama' => 'Rudi Hartono',
                'jabatan' => 'Petugas Surveilans Epidemiologi',
                'unit_kerja' => 'Surveilans',
                'no_hp' => '081210450107',
                'alamat' => 'Jl. Cendana Raya No. 21, Balaraja, Tangerang',
                'is_aktif' => true,
                'email' => 'rudi.hartono@puskesmasbunar.id',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '199512022018102008',
                'nama' => 'Lina Marlina',
                'jabatan' => 'Sanitarian',
                'unit_kerja' => 'Kesehatan Lingkungan',
                'no_hp' => '081210450108',
                'alamat' => 'Taman Bunar Residence Blok D2, Tangerang',
                'is_aktif' => true,
                'email' => 'lina.marlina@puskesmasbunar.id',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '199001162013022009',
                'nama' => 'Fajar Nugroho',
                'jabatan' => 'Analis Laboratorium',
                'unit_kerja' => 'Laboratorium',
                'no_hp' => '081210450109',
                'alamat' => 'Perum Balaraja Harmoni Blok B10, Tangerang',
                'is_aktif' => true,
                'email' => 'fajar.nugroho@puskesmasbunar.id',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '198805092011012010',
                'nama' => 'Tuti Wulandari',
                'jabatan' => 'Apoteker Penanggung Jawab',
                'unit_kerja' => 'Farmasi',
                'no_hp' => '081210450110',
                'alamat' => 'Jl. Anggrek Permai No. 5, Tangerang',
                'is_aktif' => true,
                'email' => 'tuti.wulandari@puskesmasbunar.id',
                'role_kode' => 'pegawai',
            ],
        ])->map(function (array $data) use ($roleIds) {
            $pegawai = Pegawai::query()->create([
                'nip' => $data['nip'],
                'nama' => $data['nama'],
                'jabatan' => $data['jabatan'],
                'unit_kerja' => $data['unit_kerja'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'is_aktif' => $data['is_aktif'],
            ]);

            $user = User::query()->create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'role_id' => $roleIds[$data['role_kode']],
                'pegawai_id' => $pegawai->id,
                'password' => 'password',
            ]);

            return [
                'pegawai' => $pegawai,
                'user' => $user,
                'role_kode' => $data['role_kode'],
            ];
        });
    }

    /**
     * @return \Illuminate\Support\Collection<string, \App\Models\Kegiatan>
     */
    private function seedKegiatan(): Collection
    {
        return collect([
            [
                'nama_kegiatan' => 'Pendaftaran dan Rekam Medis',
                'jenis' => 'layanan',
                'deskripsi' => 'Pelayanan registrasi pasien, verifikasi kepesertaan, dan pencatatan rekam medis harian.',
            ],
            [
                'nama_kegiatan' => 'Skrining Kesehatan BPJS / CKG',
                'jenis' => 'layanan',
                'deskripsi' => 'Skrining faktor risiko penyakit tidak menular dan pemeriksaan kesehatan dasar peserta BPJS.',
            ],
            [
                'nama_kegiatan' => 'PIPP',
                'jenis' => 'layanan',
                'deskripsi' => 'Pemberian informasi, penanganan pengaduan, dan pelayanan pelanggan puskesmas.',
            ],
            [
                'nama_kegiatan' => 'Kluster 2 Kesehatan Ibu',
                'jenis' => 'layanan',
                'deskripsi' => 'Pemeriksaan antenatal, konseling ibu hamil, dan pemantauan risiko kehamilan.',
            ],
            [
                'nama_kegiatan' => 'Kluster 2 Balita dan Anak',
                'jenis' => 'layanan',
                'deskripsi' => 'Pemantauan tumbuh kembang, imunisasi, dan edukasi kesehatan balita.',
            ],
            [
                'nama_kegiatan' => 'Meja Tensi dan Triage',
                'jenis' => 'layanan',
                'deskripsi' => 'Pemeriksaan tanda vital awal sebelum pasien masuk ke poli tujuan.',
            ],
            [
                'nama_kegiatan' => 'Kluster 3 Pelayanan Dewasa',
                'jenis' => 'layanan',
                'deskripsi' => 'Pelayanan pemeriksaan umum pasien dewasa dengan kasus rawat jalan.',
            ],
            [
                'nama_kegiatan' => 'Kluster 3 Layanan Lansia',
                'jenis' => 'layanan',
                'deskripsi' => 'Pelayanan kesehatan lansia mencakup skrining geriatri dan pemantauan penyakit kronis.',
            ],
            [
                'nama_kegiatan' => 'Pelayanan TB Terpadu',
                'jenis' => 'layanan',
                'deskripsi' => 'Penemuan kasus, pemantauan terapi, dan edukasi pasien tuberkulosis.',
            ],
            [
                'nama_kegiatan' => 'Pelayanan UGD',
                'jenis' => 'layanan',
                'deskripsi' => 'Pelayanan kegawatdaruratan dasar dan stabilisasi pasien sebelum rujukan.',
            ],
            [
                'nama_kegiatan' => 'Pelayanan Laboratorium',
                'jenis' => 'layanan',
                'deskripsi' => 'Pemeriksaan laboratorium dasar untuk mendukung diagnosis dan tindak lanjut layanan.',
            ],
            [
                'nama_kegiatan' => 'Pelayanan Farmasi',
                'jenis' => 'layanan',
                'deskripsi' => 'Dispensing obat, edukasi penggunaan obat, dan monitoring ketersediaan farmasi.',
            ],
            [
                'nama_kegiatan' => 'Supervisi Sanitasi Sekolah',
                'jenis' => 'dinas_luar',
                'deskripsi' => 'Pemantauan sarana sanitasi, air bersih, dan kebersihan lingkungan sekolah di wilayah kerja.',
            ],
            [
                'nama_kegiatan' => 'Investigasi Epidemiologi DBD',
                'jenis' => 'dinas_luar',
                'deskripsi' => 'Pelacakan kasus demam berdarah, asesmen lingkungan, dan koordinasi pemberantasan sarang nyamuk.',
            ],
            [
                'nama_kegiatan' => 'Pembinaan Posyandu Remaja',
                'jenis' => 'dinas_luar',
                'deskripsi' => 'Pendampingan kader dan edukasi kesehatan reproduksi pada kelompok remaja.',
            ],
        ])->mapWithKeys(function (array $data) {
            $kegiatan = Kegiatan::query()->create($data + ['is_aktif' => true]);

            return [$kegiatan->nama_kegiatan => $kegiatan];
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \App\Models\Kegiatan>  $kegiatanByName
     * @param  \Illuminate\Support\Collection<string, \App\Models\Pegawai>  $pegawaiByName
     * @param  \Illuminate\Support\Collection<string, \App\Models\User>  $usersByRole
     * @return \Illuminate\Support\Collection<int, \App\Models\Jadwal>
     */
    private function seedOperationalJadwal(
        Collection $kegiatanByName,
        Collection $pegawaiByName,
        Collection $usersByRole
    ): Collection {
        $today = Carbon::today();
        $adminUser = $usersByRole['admin_Nur Aisyah Pratama'];
        $pjUser = $usersByRole['pj_penjadwalan_Rina Fitriani'];

        return collect([
            [
                'kegiatan' => 'Kluster 3 Pelayanan Dewasa',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->subDays(8),
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '11:30:00',
                'lokasi' => 'Poli Umum Gedung Utama',
                'keterangan' => 'Pelayanan rawat jalan pasien dewasa dengan fokus hipertensi, ISPA, dan kontrol diabetes.',
                'status' => 'selesai',
                'petugas' => [
                    ['nama' => 'Dewi Lestari', 'peran_tugas' => 'Perawat triase', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Rina Fitriani', 'peran_tugas' => 'Koordinator pelayanan', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'Kluster 2 Balita dan Anak',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->subDays(5),
                'waktu_mulai' => '08:30:00',
                'waktu_selesai' => '11:30:00',
                'lokasi' => 'Posyandu Melati RW 03',
                'keterangan' => 'Pelayanan imunisasi dasar lengkap dan pemantauan tumbuh kembang bayi usia 0 sampai 24 bulan.',
                'status' => 'selesai',
                'petugas' => [
                    ['nama' => 'Siska Handayani', 'peran_tugas' => 'Petugas imunisasi', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Maya Puspitasari', 'peran_tugas' => 'Konseling gizi balita', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Andi Saputra', 'peran_tugas' => 'Edukasi orang tua', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'PIPP',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->subDays(2),
                'waktu_mulai' => '13:00:00',
                'waktu_selesai' => '15:00:00',
                'lokasi' => 'Ruang Pertemuan Puskesmas Bunar',
                'keterangan' => 'Sosialisasi alur layanan, pemanfaatan antrean digital, dan penanganan keluhan pasien rawat jalan.',
                'status' => 'selesai',
                'petugas' => [
                    ['nama' => 'Andi Saputra', 'peran_tugas' => 'Moderator layanan pelanggan', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Nur Aisyah Pratama', 'peran_tugas' => 'Penanggung jawab administrasi', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'Pelayanan Laboratorium',
                'creator_id' => $adminUser->id,
                'tanggal' => $today->copy()->subDay(),
                'waktu_mulai' => '07:30:00',
                'waktu_selesai' => '10:30:00',
                'lokasi' => 'Laboratorium Puskesmas Bunar',
                'keterangan' => 'Pemeriksaan gula darah, kolesterol, dan hemoglobin untuk pasien program penyakit kronis.',
                'status' => 'selesai',
                'petugas' => [
                    ['nama' => 'Fajar Nugroho', 'peran_tugas' => 'Analis laboratorium', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Dewi Lestari', 'peran_tugas' => 'Petugas pendamping pasien', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'Kluster 3 Pelayanan Dewasa',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy(),
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '12:00:00',
                'lokasi' => 'Poli Umum Gedung Utama',
                'keterangan' => 'Pelayanan rutin hari kerja dengan fokus pemeriksaan pasien dewasa dan tindak lanjut resep.',
                'status' => 'berjalan',
                'petugas' => [
                    ['nama' => 'Dewi Lestari', 'peran_tugas' => 'Perawat triase', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Rina Fitriani', 'peran_tugas' => 'Koordinator shift', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'Kluster 3 Layanan Lansia',
                'creator_id' => $adminUser->id,
                'tanggal' => $today->copy()->addDay(),
                'waktu_mulai' => '08:30:00',
                'waktu_selesai' => '11:00:00',
                'lokasi' => 'Balai Warga Bunar Barat',
                'keterangan' => 'Skrining tekanan darah, gula darah sewaktu, dan edukasi minum obat teratur untuk lansia.',
                'status' => 'terjadwal',
                'petugas' => [
                    ['nama' => 'Dewi Lestari', 'peran_tugas' => 'Pemeriksaan dasar', 'status_penugasan' => 'dijadwalkan'],
                    ['nama' => 'Maya Puspitasari', 'peran_tugas' => 'Konseling diet lansia', 'status_penugasan' => 'dijadwalkan'],
                ],
            ],
            [
                'kegiatan' => 'Supervisi Sanitasi Sekolah',
                'creator_id' => $adminUser->id,
                'tanggal' => $today->copy()->addDays(3),
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '11:30:00',
                'lokasi' => 'SDN Bunar 01',
                'keterangan' => 'Audit jamban, sarana cuci tangan, dan pengelolaan sampah sekolah menjelang penilaian UKS.',
                'status' => 'terjadwal',
                'petugas' => [
                    ['nama' => 'Lina Marlina', 'peran_tugas' => 'Sanitarian lapangan', 'status_penugasan' => 'dijadwalkan'],
                    ['nama' => 'Rudi Hartono', 'peran_tugas' => 'Dokumentasi dan observasi', 'status_penugasan' => 'dijadwalkan'],
                ],
            ],
            [
                'kegiatan' => 'Investigasi Epidemiologi DBD',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->addDays(5),
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '12:00:00',
                'lokasi' => 'Kampung Bunar RT 05/RW 02',
                'keterangan' => 'Kunjungan rumah kasus suspek DBD dan edukasi pemberantasan sarang nyamuk bersama kader.',
                'status' => 'draft',
                'petugas' => [
                    ['nama' => 'Rudi Hartono', 'peran_tugas' => 'Petugas surveilans', 'status_penugasan' => 'dijadwalkan'],
                    ['nama' => 'Andi Saputra', 'peran_tugas' => 'Edukasi warga', 'status_penugasan' => 'dijadwalkan'],
                ],
            ],
            [
                'kegiatan' => 'Pembinaan Posyandu Remaja',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->addDays(8),
                'waktu_mulai' => '13:00:00',
                'waktu_selesai' => '15:30:00',
                'lokasi' => 'SMK Kesehatan Balaraja',
                'keterangan' => 'Pendampingan kader sebaya dan penyuluhan kesehatan reproduksi serta gizi seimbang remaja.',
                'status' => 'terjadwal',
                'petugas' => [
                    ['nama' => 'Siska Handayani', 'peran_tugas' => 'Narasumber kesehatan reproduksi', 'status_penugasan' => 'dijadwalkan'],
                    ['nama' => 'Andi Saputra', 'peran_tugas' => 'Fasilitator diskusi', 'status_penugasan' => 'dijadwalkan'],
                ],
            ],
        ])->map(function (array $data) use ($kegiatanByName, $pegawaiByName) {
            $jadwal = Jadwal::query()->create([
                'kegiatan_id' => $kegiatanByName[$data['kegiatan']]->id,
                'created_by' => $data['creator_id'],
                'tanggal' => $data['tanggal']->toDateString(),
                'waktu_mulai' => $data['waktu_mulai'],
                'waktu_selesai' => $data['waktu_selesai'],
                'lokasi' => $data['lokasi'],
                'keterangan' => $data['keterangan'],
                'status' => $data['status'],
            ]);

            foreach ($data['petugas'] as $petugas) {
                JadwalPegawai::query()->create([
                    'jadwal_id' => $jadwal->id,
                    'pegawai_id' => $pegawaiByName[$petugas['nama']]->id,
                    'peran_tugas' => $petugas['peran_tugas'],
                    'status_penugasan' => $petugas['status_penugasan'],
                ]);
            }

            return $jadwal->load(['pegawai', 'kegiatan']);
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \App\Models\Pegawai>  $pegawaiByName
     * @param  \Illuminate\Support\Collection<string, \App\Models\User>  $usersByRole
     */
    private function seedPengajuanDinas(Collection $pegawaiByName, Collection $usersByRole): void
    {
        $today = Carbon::today();
        $adminUser = $usersByRole['admin_Nur Aisyah Pratama'];
        $pjUser = $usersByRole['pj_penjadwalan_Rina Fitriani'];

        collect([
            [
                'pegawai' => 'Dewi Lestari',
                'tanggal_pengajuan' => $today->copy()->subDays(12),
                'tanggal_mulai' => $today->copy()->subDays(7),
                'tanggal_selesai' => $today->copy()->subDays(7),
                'tujuan' => 'Desa Saga, Kecamatan Balaraja',
                'kegiatan' => 'Pendampingan Posbindu PTM',
                'keterangan' => 'Mendampingi pemeriksaan tekanan darah, edukasi diet rendah garam, dan pencatatan peserta baru.',
                'status' => 'disetujui',
                'diverifikasi_oleh' => $pjUser->id,
                'diverifikasi_at' => $today->copy()->subDays(11)->setTime(9, 10),
                'catatan_verifikasi' => 'Disetujui, pastikan membawa tensimeter digital dan format pelaporan peserta.',
            ],
            [
                'pegawai' => 'Rudi Hartono',
                'tanggal_pengajuan' => $today->copy()->subDays(6),
                'tanggal_mulai' => $today->copy()->subDays(1),
                'tanggal_selesai' => $today->copy()->addDay(),
                'tujuan' => 'Kelurahan Bunar, RW 02',
                'kegiatan' => 'Investigasi Epidemiologi Kasus DBD',
                'keterangan' => 'Koordinasi dengan kader jumantik dan pemeriksaan lingkungan pada rumah indeks serta rumah sekitar.',
                'status' => 'disetujui',
                'diverifikasi_oleh' => $adminUser->id,
                'diverifikasi_at' => $today->copy()->subDays(5)->setTime(10, 0),
                'catatan_verifikasi' => 'Prioritaskan wilayah dengan laporan kasus demam dalam tujuh hari terakhir.',
            ],
            [
                'pegawai' => 'Lina Marlina',
                'tanggal_pengajuan' => $today->copy()->subDays(3),
                'tanggal_mulai' => $today->copy()->addDays(2),
                'tanggal_selesai' => $today->copy()->addDays(2),
                'tujuan' => 'SDN Bunar 02',
                'kegiatan' => 'Pembinaan Sanitasi Sekolah',
                'keterangan' => 'Pemeriksaan kualitas jamban siswa, kebersihan kantin, dan kecukupan sarana cuci tangan.',
                'status' => 'diajukan',
                'diverifikasi_oleh' => null,
                'diverifikasi_at' => null,
                'catatan_verifikasi' => null,
            ],
            [
                'pegawai' => 'Maya Puspitasari',
                'tanggal_pengajuan' => $today->copy()->subDays(9),
                'tanggal_mulai' => $today->copy()->addDays(6),
                'tanggal_selesai' => $today->copy()->addDays(7),
                'tujuan' => 'Balai Desa Bunar',
                'kegiatan' => 'Pelatihan PMT Lokal untuk Kader Posyandu',
                'keterangan' => 'Pelatihan menu tambahan berbahan pangan lokal untuk balita dengan berat badan kurang.',
                'status' => 'ditolak',
                'diverifikasi_oleh' => $pjUser->id,
                'diverifikasi_at' => $today->copy()->subDays(8)->setTime(14, 20),
                'catatan_verifikasi' => 'Ditunda karena jadwal berbenturan dengan pelayanan gizi terpadu di wilayah lain.',
            ],
            [
                'pegawai' => 'Andi Saputra',
                'tanggal_pengajuan' => $today->copy()->subDays(4),
                'tanggal_mulai' => $today->copy()->addDays(10),
                'tanggal_selesai' => $today->copy()->addDays(10),
                'tujuan' => 'RW 05 Desa Bunar',
                'kegiatan' => 'Edukasi PHBS Remaja',
                'keterangan' => 'Sesi penyuluhan perilaku hidup bersih dan sehat bagi karang taruna setempat.',
                'status' => 'dibatalkan',
                'diverifikasi_oleh' => $adminUser->id,
                'diverifikasi_at' => $today->copy()->subDays(3)->setTime(16, 5),
                'catatan_verifikasi' => 'Pengajuan dibatalkan oleh pengusul karena agenda warga dipindahkan.',
            ],
        ])->each(function (array $data) use ($pegawaiByName) {
            PengajuanDinas::query()->create([
                'pegawai_id' => $pegawaiByName[$data['pegawai']]->id,
                'tanggal_pengajuan' => $data['tanggal_pengajuan']->toDateString(),
                'tanggal_mulai' => $data['tanggal_mulai']->toDateString(),
                'tanggal_selesai' => $data['tanggal_selesai']->toDateString(),
                'tujuan' => $data['tujuan'],
                'kegiatan' => $data['kegiatan'],
                'keterangan' => $data['keterangan'],
                'bukti_surat_path' => null,
                'bukti_surat_nama' => null,
                'bukti_surat_mime' => null,
                'status' => $data['status'],
                'diverifikasi_oleh' => $data['diverifikasi_oleh'],
                'diverifikasi_at' => $data['diverifikasi_at'],
                'catatan_verifikasi' => $data['catatan_verifikasi'],
            ]);
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Jadwal>  $jadwalList
     * @param  \Illuminate\Support\Collection<string, \App\Models\User>  $usersByRole
     */
    private function seedMonitoringAndReports(Collection $jadwalList, Collection $usersByRole): void
    {
        $today = Carbon::today();
        $adminUser = $usersByRole['admin_Nur Aisyah Pratama'];

        $jadwalList->each(function (Jadwal $jadwal) use ($today, $adminUser) {
            foreach ($jadwal->pegawai as $pegawai) {
                $status = match ($jadwal->status) {
                    'selesai' => 'selesai',
                    'berjalan' => 'proses',
                    'dibatalkan' => 'tidak_hadir',
                    default => 'belum_mulai',
                };

                Monitoring::query()->create([
                    'jadwal_id' => $jadwal->id,
                    'pegawai_id' => $pegawai->id,
                    'status' => $status,
                    'laporan' => $status === 'belum_mulai'
                        ? null
                        : sprintf(
                            'Pemantauan %s untuk kegiatan %s di %s berjalan sesuai penugasan.',
                            $pegawai->nama,
                            $jadwal->kegiatan?->nama_kegiatan,
                            $jadwal->lokasi
                        ),
                    'dipantau_at' => match ($status) {
                        'selesai' => Carbon::parse($jadwal->tanggal)->setTime(15, 45),
                        'proses' => $today->copy()->setTime(10, 30),
                        'tidak_hadir' => Carbon::parse($jadwal->tanggal)->setTime(8, 20),
                        default => null,
                    },
                ]);
            }

            if (! in_array($jadwal->status, ['selesai', 'berjalan'], true)) {
                return;
            }

            foreach ($jadwal->pegawai->take(1) as $pegawai) {
                LaporanKegiatan::query()->create([
                    'jadwal_id' => $jadwal->id,
                    'pegawai_id' => $pegawai->id,
                    'tanggal' => $jadwal->tanggal,
                    'laporan' => sprintf(
                        'Kegiatan %s di %s terlaksana dengan baik. Peserta terlayani sesuai target shift dan tidak ada kendala operasional yang signifikan.',
                        $jadwal->kegiatan?->nama_kegiatan,
                        $jadwal->lokasi
                    ),
                    'status_verifikasi' => $jadwal->status === 'selesai' ? 'diterima' : 'menunggu',
                    'diverifikasi_oleh' => $jadwal->status === 'selesai' ? $adminUser->id : null,
                    'diverifikasi_at' => $jadwal->status === 'selesai'
                        ? Carbon::parse($jadwal->tanggal)->setTime(17, 10)
                        : null,
                    'catatan_verifikasi' => $jadwal->status === 'selesai'
                        ? 'Laporan sudah lengkap, waktu pelayanan dan hasil kegiatan sesuai monitoring.'
                        : null,
                ]);
            }
        });
    }

    private function resetApplicationTables(): void
    {
        LaporanKegiatan::query()->delete();
        Monitoring::query()->delete();
        JadwalPegawai::query()->delete();
        PengajuanDinas::query()->delete();
        Jadwal::query()->delete();
        Kegiatan::query()->delete();
        User::query()->delete();
        Pegawai::query()->delete();
    }
}
