<?php

namespace Tests\Feature\Support;

use App\Models\Jadwal;
use App\Models\Kegiatan;
use App\Models\LaporanKegiatan;
use App\Models\Pegawai;
use App\Models\PengajuanDinas;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

trait InteractsWithBlackboxData
{
    protected function createUserWithRole(string $kodeRole, array $pegawaiAttributes = [], array $userAttributes = []): User
    {
        $role = Role::query()->firstOrCreate(
            ['kode' => $kodeRole],
            ['nama' => str($kodeRole)->replace('_', ' ')->title()->toString()]
        );

        $pegawai = Pegawai::query()->create(array_merge([
            'nip' => fake()->unique()->numerify('##################'),
            'jenis_pegawai' => 'asn',
            'nama' => fake()->name(),
            'jabatan' => 'Petugas',
            'unit_kerja' => 'Puskesmas Bunar',
            'no_hp' => '081234567890',
            'alamat' => 'Kabupaten Tangerang',
            'is_aktif' => true,
        ], $pegawaiAttributes));

        return User::query()->create(array_merge([
            'name' => $pegawai->nama,
            'email' => fake()->unique()->safeEmail(),
            'role_id' => $role->id,
            'pegawai_id' => $pegawai->id,
            'password' => 'password',
        ], $userAttributes));
    }

    protected function createKegiatan(array $attributes = []): Kegiatan
    {
        return Kegiatan::query()->create(array_merge([
            'nama_kegiatan' => 'Layanan '.fake()->unique()->word(),
            'jenis' => 'layanan',
            'deskripsi' => 'Deskripsi layanan pengujian',
            'is_aktif' => true,
        ], $attributes));
    }

    protected function createJadwal(User $creator, Kegiatan $kegiatan, array $attributes = [], array $petugas = []): Jadwal
    {
        $jadwal = Jadwal::query()->create(array_merge([
            'kegiatan_id' => $kegiatan->id,
            'created_by' => $creator->id,
            'tanggal' => now()->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '10:00',
            'lokasi' => 'Poli Umum',
            'keterangan' => 'Jadwal pengujian',
            'status' => 'terjadwal',
        ], $attributes));

        $payload = collect($petugas)->mapWithKeys(function ($item) {
            $pegawai = $item instanceof Pegawai ? $item : $item['pegawai'];
            $statusPenugasan = $item['status_penugasan'] ?? 'dijadwalkan';
            $peranTugas = $item['peran_tugas'] ?? 'Petugas';

            return [
                $pegawai->id => [
                    'peran_tugas' => $peranTugas,
                    'status_penugasan' => $statusPenugasan,
                ],
            ];
        })->all();

        if ($payload !== []) {
            $jadwal->pegawai()->sync($payload);
        }

        return $jadwal->fresh(['kegiatan', 'pegawai']);
    }

    protected function createPengajuanDinas(Pegawai $pegawai, array $attributes = []): PengajuanDinas
    {
        return PengajuanDinas::query()->create(array_merge([
            'pegawai_id' => $pegawai->id,
            'tanggal_pengajuan' => now()->toDateString(),
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'tujuan' => 'Dinas Kesehatan',
            'kegiatan' => 'Koordinasi Program',
            'keterangan' => 'Pengajuan pengujian',
            'status' => 'diajukan',
        ], $attributes));
    }

    protected function createLaporanKegiatan(Pegawai $pegawai, array $attributes = []): LaporanKegiatan
    {
        return LaporanKegiatan::query()->create(array_merge([
            'jenis_kegiatan' => LaporanKegiatan::JENIS_LAYANAN,
            'jadwal_id' => null,
            'pengajuan_dinas_id' => null,
            'pegawai_id' => $pegawai->id,
            'tanggal' => now()->toDateString(),
            'laporan' => 'Laporan kegiatan pengujian',
            'status_verifikasi' => 'menunggu',
            'catatan_verifikasi' => null,
            'diverifikasi_oleh' => null,
            'diverifikasi_at' => null,
        ], $attributes));
    }

    protected function fakePdfUpload(string $name = 'dokumen.pdf'): UploadedFile
    {
        Storage::fake('public');

        return UploadedFile::fake()->create($name, 200, 'application/pdf');
    }

    protected function freezeToday(string $date = '2026-06-06 08:00:00'): void
    {
        Carbon::setTestNow(Carbon::parse($date));
    }
}
