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
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $this->resetApplicationTables();

        $roleIds = [
            'admin' => DB::table('roles')->where('kode', 'admin')->value('id'),
            'pj_penjadwalan' => DB::table('roles')->where('kode', 'pj_penjadwalan')->value('id'),
            'pegawai' => DB::table('roles')->where('kode', 'pegawai')->value('id'),
        ];

        $pegawaiRecords = collect([
            [
                'nip' => '197901012006041001',
                'nama' => 'Admin Puskesmas',
                'jabatan' => 'Administrator Sistem',
                'unit_kerja' => 'Tata Usaha',
                'no_hp' => '081234567890',
                'alamat' => 'Puskesmas Bunar, Tangerang',
                'is_aktif' => true,
                'email' => 'admin@pkmbunar.test',
                'role_kode' => 'admin',
            ],
            [
                'nip' => '198402102009122001',
                'nama' => 'Rani Penjadwalan',
                'jabatan' => 'PJ Penjadwalan',
                'unit_kerja' => 'Pelayanan',
                'no_hp' => '081234567891',
                'alamat' => 'Perumahan Sehat Bunar',
                'is_aktif' => true,
                'email' => 'pj@pkmbunar.test',
                'role_kode' => 'pj_penjadwalan',
            ],
            [
                'nip' => '199105052015031002',
                'nama' => 'Dina Pegawai',
                'jabatan' => 'Perawat',
                'unit_kerja' => 'Poli Umum',
                'no_hp' => '081234567892',
                'alamat' => 'Bunar Asri Blok A2',
                'is_aktif' => true,
                'email' => 'pegawai@pkmbunar.test',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '198908142014022003',
                'nama' => 'Siska Imunisasi',
                'jabatan' => 'Bidan',
                'unit_kerja' => 'KIA',
                'no_hp' => '081234567893',
                'alamat' => 'Kampung Bunar Tengah',
                'is_aktif' => true,
                'email' => 'siska@pkmbunar.test',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '199211072016052004',
                'nama' => 'Andi Promkes',
                'jabatan' => 'Promosi Kesehatan',
                'unit_kerja' => 'Promkes',
                'no_hp' => '081234567894',
                'alamat' => 'Perum Griya Bunar',
                'is_aktif' => true,
                'email' => 'andi@pkmbunar.test',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '199307192017082005',
                'nama' => 'Maya Gizi',
                'jabatan' => 'Ahli Gizi',
                'unit_kerja' => 'Gizi',
                'no_hp' => '081234567895',
                'alamat' => 'Komplek Puskesmas Bunar',
                'is_aktif' => true,
                'email' => 'maya@pkmbunar.test',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '198711232012011006',
                'nama' => 'Rudi Surveilans',
                'jabatan' => 'Petugas Surveilans',
                'unit_kerja' => 'Surveilans',
                'no_hp' => '081234567896',
                'alamat' => 'Bunar Indah RT 04',
                'is_aktif' => true,
                'email' => 'rudi@pkmbunar.test',
                'role_kode' => 'pegawai',
            ],
            [
                'nip' => '199512022018102007',
                'nama' => 'Lina Kesling',
                'jabatan' => 'Sanitarian',
                'unit_kerja' => 'Kesehatan Lingkungan',
                'no_hp' => '081234567897',
                'alamat' => 'Taman Bunar Residence',
                'is_aktif' => true,
                'email' => 'lina@pkmbunar.test',
                'role_kode' => 'pegawai',
            ],
        ])->map(function (array $data) use ($roleIds) {
            $pegawai = Pegawai::create([
                'nip' => $data['nip'],
                'nama' => $data['nama'],
                'jabatan' => $data['jabatan'],
                'unit_kerja' => $data['unit_kerja'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'is_aktif' => $data['is_aktif'],
            ]);

            $user = User::create([
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

        $usersByRole = $pegawaiRecords->mapWithKeys(fn (array $item) => [$item['role_kode'].'_'.$item['pegawai']->nama => $item['user']]);
        $pegawaiByName = $pegawaiRecords->mapWithKeys(fn (array $item) => [$item['pegawai']->nama => $item['pegawai']]);

        $kegiatanList = collect([
            ['nama_kegiatan' => 'Pelayanan Poli Umum Pagi', 'jenis' => 'layanan', 'deskripsi' => 'Pelayanan pasien umum pagi hari.'],
            ['nama_kegiatan' => 'Imunisasi Keliling Posyandu', 'jenis' => 'layanan', 'deskripsi' => 'Pelayanan imunisasi di posyandu wilayah kerja.'],
            ['nama_kegiatan' => 'Penyuluhan Gizi Balita', 'jenis' => 'layanan', 'deskripsi' => 'Penyuluhan gizi untuk ibu dan balita.'],
            ['nama_kegiatan' => 'Kunjungan Rumah Lansia', 'jenis' => 'layanan', 'deskripsi' => 'Pendampingan kesehatan lansia di rumah.'],
            ['nama_kegiatan' => 'Supervisi Sanitasi Sekolah', 'jenis' => 'dinas_luar', 'deskripsi' => 'Monitoring sanitasi ke sekolah sekitar wilayah puskesmas.'],
            ['nama_kegiatan' => 'Pelacakan Kasus DBD', 'jenis' => 'dinas_luar', 'deskripsi' => 'Investigasi epidemiologi kasus DBD di lapangan.'],
        ])->map(fn (array $data) => Kegiatan::create($data));

        $kegiatanByName = $kegiatanList->keyBy('nama_kegiatan');
        $adminUser = $pegawaiRecords->firstWhere('role_kode', 'admin')['user'];
        $pjUser = $pegawaiRecords->firstWhere('role_kode', 'pj_penjadwalan')['user'];

        $today = Carbon::today();

        $jadwalList = collect([
            [
                'kegiatan' => 'Pelayanan Poli Umum Pagi',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->subDays(6),
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '11:00:00',
                'lokasi' => 'Poli Umum',
                'keterangan' => 'Pelayanan pasien umum dengan fokus penyakit musiman.',
                'status' => 'selesai',
                'petugas' => [
                    ['nama' => 'Dina Pegawai', 'peran_tugas' => 'Perawat pendamping', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Rani Penjadwalan', 'peran_tugas' => 'Koordinator layanan', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'Imunisasi Keliling Posyandu',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->subDays(2),
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '11:30:00',
                'lokasi' => 'Posyandu Melati',
                'keterangan' => 'Target balita usia 0-24 bulan.',
                'status' => 'selesai',
                'petugas' => [
                    ['nama' => 'Siska Imunisasi', 'peran_tugas' => 'Petugas imunisasi', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Andi Promkes', 'peran_tugas' => 'Edukasi orang tua', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'Penyuluhan Gizi Balita',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->subDay(),
                'waktu_mulai' => '13:00:00',
                'waktu_selesai' => '15:00:00',
                'lokasi' => 'Balai RW 03',
                'keterangan' => 'Sosialisasi menu sehat keluarga.',
                'status' => 'selesai',
                'petugas' => [
                    ['nama' => 'Maya Gizi', 'peran_tugas' => 'Narasumber utama', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Andi Promkes', 'peran_tugas' => 'Moderator', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'Pelayanan Poli Umum Pagi',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy(),
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '12:00:00',
                'lokasi' => 'Poli Umum',
                'keterangan' => 'Pelayanan rutin hari ini.',
                'status' => 'berjalan',
                'petugas' => [
                    ['nama' => 'Dina Pegawai', 'peran_tugas' => 'Perawat triase', 'status_penugasan' => 'hadir'],
                    ['nama' => 'Rani Penjadwalan', 'peran_tugas' => 'Pemantauan operasional', 'status_penugasan' => 'hadir'],
                ],
            ],
            [
                'kegiatan' => 'Kunjungan Rumah Lansia',
                'creator_id' => $adminUser->id,
                'tanggal' => $today->copy(),
                'waktu_mulai' => '14:00:00',
                'waktu_selesai' => '16:00:00',
                'lokasi' => 'Wilayah Bunar Barat',
                'keterangan' => 'Pemantauan kesehatan lansia prioritas.',
                'status' => 'terjadwal',
                'petugas' => [
                    ['nama' => 'Dina Pegawai', 'peran_tugas' => 'Pemeriksaan dasar', 'status_penugasan' => 'dijadwalkan'],
                    ['nama' => 'Lina Kesling', 'peran_tugas' => 'Pendamping keluarga', 'status_penugasan' => 'dijadwalkan'],
                ],
            ],
            [
                'kegiatan' => 'Supervisi Sanitasi Sekolah',
                'creator_id' => $adminUser->id,
                'tanggal' => $today->copy()->addDays(2),
                'waktu_mulai' => '08:30:00',
                'waktu_selesai' => '11:00:00',
                'lokasi' => 'SDN Bunar 01',
                'keterangan' => 'Pemeriksaan sarana sanitasi sekolah dasar.',
                'status' => 'terjadwal',
                'petugas' => [
                    ['nama' => 'Lina Kesling', 'peran_tugas' => 'Sanitarian', 'status_penugasan' => 'dijadwalkan'],
                    ['nama' => 'Rudi Surveilans', 'peran_tugas' => 'Dokumentasi', 'status_penugasan' => 'dijadwalkan'],
                ],
            ],
            [
                'kegiatan' => 'Pelacakan Kasus DBD',
                'creator_id' => $adminUser->id,
                'tanggal' => $today->copy()->addDays(4),
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '12:00:00',
                'lokasi' => 'RT 05 / RW 02',
                'keterangan' => 'Investigasi epidemiologi dan edukasi warga.',
                'status' => 'draft',
                'petugas' => [
                    ['nama' => 'Rudi Surveilans', 'peran_tugas' => 'Petugas lapangan', 'status_penugasan' => 'dijadwalkan'],
                    ['nama' => 'Andi Promkes', 'peran_tugas' => 'Edukasi warga', 'status_penugasan' => 'dijadwalkan'],
                ],
            ],
            [
                'kegiatan' => 'Imunisasi Keliling Posyandu',
                'creator_id' => $pjUser->id,
                'tanggal' => $today->copy()->addDays(7),
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '11:30:00',
                'lokasi' => 'Posyandu Anggrek',
                'keterangan' => 'Persiapan kegiatan imunisasi minggu depan.',
                'status' => 'terjadwal',
                'petugas' => [
                    ['nama' => 'Siska Imunisasi', 'peran_tugas' => 'Petugas imunisasi', 'status_penugasan' => 'dijadwalkan'],
                    ['nama' => 'Maya Gizi', 'peran_tugas' => 'Konseling gizi', 'status_penugasan' => 'dijadwalkan'],
                ],
            ],
        ])->map(function (array $data) use ($kegiatanByName, $pegawaiByName) {
            $jadwal = Jadwal::create([
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
                JadwalPegawai::create([
                    'jadwal_id' => $jadwal->id,
                    'pegawai_id' => $pegawaiByName[$petugas['nama']]->id,
                    'peran_tugas' => $petugas['peran_tugas'],
                    'status_penugasan' => $petugas['status_penugasan'],
                ]);
            }

            return $jadwal->load('pegawai');
        });

        collect([
            [
                'pegawai' => 'Dina Pegawai',
                'tanggal_pengajuan' => $today->copy()->subDays(10),
                'tanggal_mulai' => $today->copy()->subDays(5),
                'tanggal_selesai' => $today->copy()->subDays(4),
                'tujuan' => 'Kecamatan Balaraja',
                'kegiatan' => 'Pendampingan Posbindu',
                'keterangan' => 'Mendukung pelaksanaan posbindu terpadu.',
                'status' => 'disetujui',
                'diverifikasi_oleh' => $pjUser->id,
                'diverifikasi_at' => $today->copy()->subDays(9)->setTime(9, 30),
                'catatan_verifikasi' => 'Silakan siapkan perlengkapan pemeriksaan dasar.',
            ],
            [
                'pegawai' => 'Rudi Surveilans',
                'tanggal_pengajuan' => $today->copy()->subDays(4),
                'tanggal_mulai' => $today->copy()->subDay(),
                'tanggal_selesai' => $today->copy()->addDay(),
                'tujuan' => 'Desa Bunar',
                'kegiatan' => 'Investigasi Kasus DBD',
                'keterangan' => 'Koordinasi dengan kader setempat.',
                'status' => 'disetujui',
                'diverifikasi_oleh' => $adminUser->id,
                'diverifikasi_at' => $today->copy()->subDays(3)->setTime(10, 0),
                'catatan_verifikasi' => 'Utamakan keluarga dengan kasus aktif.',
            ],
            [
                'pegawai' => 'Lina Kesling',
                'tanggal_pengajuan' => $today->copy()->subDays(2),
                'tanggal_mulai' => $today->copy()->addDays(3),
                'tanggal_selesai' => $today->copy()->addDays(3),
                'tujuan' => 'SDN Bunar 02',
                'kegiatan' => 'Edukasi Sanitasi Sekolah',
                'keterangan' => 'Pemeriksaan jamban dan sarana cuci tangan.',
                'status' => 'diajukan',
                'diverifikasi_oleh' => null,
                'diverifikasi_at' => null,
                'catatan_verifikasi' => null,
            ],
            [
                'pegawai' => 'Maya Gizi',
                'tanggal_pengajuan' => $today->copy()->subDays(7),
                'tanggal_mulai' => $today->copy()->addDays(5),
                'tanggal_selesai' => $today->copy()->addDays(6),
                'tujuan' => 'Balai Desa Bunar',
                'kegiatan' => 'Pelatihan PMT Lokal',
                'keterangan' => 'Kolaborasi dengan kader posyandu.',
                'status' => 'ditolak',
                'diverifikasi_oleh' => $pjUser->id,
                'diverifikasi_at' => $today->copy()->subDays(6)->setTime(13, 15),
                'catatan_verifikasi' => 'Jadwal berbenturan dengan agenda pelayanan rutin.',
            ],
            [
                'pegawai' => 'Andi Promkes',
                'tanggal_pengajuan' => $today->copy()->subDays(3),
                'tanggal_mulai' => $today->copy()->addDays(8),
                'tanggal_selesai' => $today->copy()->addDays(9),
                'tujuan' => 'RW 05',
                'kegiatan' => 'Kampanye PHBS',
                'keterangan' => 'Pendekatan ke komunitas remaja setempat.',
                'status' => 'dibatalkan',
                'diverifikasi_oleh' => $adminUser->id,
                'diverifikasi_at' => $today->copy()->subDays(2)->setTime(15, 0),
                'catatan_verifikasi' => 'Dibatalkan atas permintaan pengusul.',
            ],
        ])->each(function (array $data) use ($pegawaiByName) {
            PengajuanDinas::create([
                'pegawai_id' => $pegawaiByName[$data['pegawai']]->id,
                'tanggal_pengajuan' => $data['tanggal_pengajuan']->toDateString(),
                'tanggal_mulai' => $data['tanggal_mulai']->toDateString(),
                'tanggal_selesai' => $data['tanggal_selesai']->toDateString(),
                'tujuan' => $data['tujuan'],
                'kegiatan' => $data['kegiatan'],
                'keterangan' => $data['keterangan'],
                'status' => $data['status'],
                'diverifikasi_oleh' => $data['diverifikasi_oleh'],
                'diverifikasi_at' => $data['diverifikasi_at'],
                'catatan_verifikasi' => $data['catatan_verifikasi'],
            ]);
        });

        $jadwalList->each(function (Jadwal $jadwal) use ($today, $adminUser) {
            foreach ($jadwal->pegawai as $pegawai) {
                $status = match ($jadwal->status) {
                    'selesai' => 'selesai',
                    'berjalan' => 'proses',
                    'dibatalkan' => 'tidak_hadir',
                    default => 'belum_mulai',
                };

                Monitoring::create([
                    'jadwal_id' => $jadwal->id,
                    'pegawai_id' => $pegawai->id,
                    'status' => $status,
                    'laporan' => $status === 'belum_mulai'
                        ? null
                        : 'Monitoring untuk '.$pegawai->nama.' pada kegiatan '.$jadwal->kegiatan?->nama_kegiatan.'.',
                    'dipantau_at' => match ($status) {
                        'selesai' => Carbon::parse($jadwal->tanggal)->setTime(16, 0),
                        'proses' => $today->copy()->setTime(10, 15),
                        'tidak_hadir' => Carbon::parse($jadwal->tanggal)->setTime(8, 30),
                        default => null,
                    },
                ]);
            }

            if (in_array($jadwal->status, ['selesai', 'berjalan'], true)) {
                foreach ($jadwal->pegawai->take(1) as $pegawai) {
                    LaporanKegiatan::create([
                        'jadwal_id' => $jadwal->id,
                        'pegawai_id' => $pegawai->id,
                        'tanggal' => $jadwal->tanggal,
                        'laporan' => 'Laporan kegiatan '.$jadwal->kegiatan?->nama_kegiatan.' di '.$jadwal->lokasi.' telah diinput oleh '.$pegawai->nama.'.',
                        'status_verifikasi' => $jadwal->status === 'selesai' ? 'diterima' : 'menunggu',
                        'diverifikasi_oleh' => $jadwal->status === 'selesai' ? $adminUser->id : null,
                        'diverifikasi_at' => $jadwal->status === 'selesai'
                            ? Carbon::parse($jadwal->tanggal)->setTime(17, 15)
                            : null,
                        'catatan_verifikasi' => $jadwal->status === 'selesai'
                            ? 'Laporan lengkap dan dapat diterima.'
                            : null,
                    ]);
                }
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
