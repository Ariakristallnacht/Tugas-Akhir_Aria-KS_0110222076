<?php

namespace Tests\Feature\Blackbox;

use App\Models\LaporanKegiatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Support\InteractsWithBlackboxData;
use Tests\TestCase;

class AdminBlackboxTest extends TestCase
{
    use InteractsWithBlackboxData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeToday();
    }

    public function test_admin_blackbox_scenarios(): void
    {
        $admin = $this->createUserWithRole('admin', ['nama' => 'Admin Bunar'], ['email' => 'admin@test.local']);
        $pj = $this->createUserWithRole('pj_penjadwalan', ['nama' => 'Rina PJ']);
        $pegawai = $this->createUserWithRole('pegawai', ['nama' => 'Dewi Pegawai']);

        $kegiatan = $this->createKegiatan(['nama_kegiatan' => 'Pelayanan Poli Umum']);
        $jadwal = $this->createJadwal($pj, $kegiatan, [
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Ruang Poli A',
        ], [$pegawai->pegawai]);

        $pengajuanDisetujui = $this->createPengajuanDinas($pegawai->pegawai, [
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'disetujui',
            'kegiatan' => 'Dinas Luar Pegawai',
        ]);

        $laporan = $this->createLaporanKegiatan($pegawai->pegawai, [
            'jadwal_id' => $jadwal->id,
            'jenis_kegiatan' => LaporanKegiatan::JENIS_LAYANAN,
            'laporan' => 'Laporan layanan poli umum',
        ]);

        $loginResponse = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);

        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertViewHas('totalPegawai', 3);
        $dashboardResponse->assertViewHas('jadwalHariIni', 1);
        $dashboardResponse->assertViewHas('laporanMenunggu', 1);

        $monitoringJadwalResponse = $this->actingAs($admin)->get(route('admin.monitoring-jadwal'));
        $monitoringJadwalResponse->assertOk();
        $monitoringJadwalResponse->assertViewHas('items', function ($items) use ($jadwal, $pengajuanDisetujui) {
            return $items->pluck('key')->contains('jadwal-'.$jadwal->id)
                && $items->pluck('key')->contains('dinas-'.$pengajuanDisetujui->id);
        });

        $calendarDetailResponse = $this->actingAs($admin)->get(route('admin.monitoring-jadwal', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));
        $calendarDetailResponse->assertOk();
        $calendarDetailResponse->assertViewHas('activePegawaiForModal', function (array $pegawaiModal) use ($pegawai) {
            return collect($pegawaiModal)->contains(fn (array $item) => $item['id'] === $pegawai->pegawai->id);
        });

        $pdfResponse = $this->actingAs($admin)->get(route('jadwal-kegiatan.export-global', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type', ''));

        $rolePegawai = $pegawai->role;
        $storePegawaiResponse = $this->actingAs($admin)->post(route('admin.pegawai.store'), [
            'jenis_pegawai' => 'asn',
            'nip' => '198701012026010001',
            'nama' => 'Pegawai Baru',
            'jabatan' => 'Perawat',
            'unit_kerja' => 'Rawat Jalan',
            'no_hp' => '081111111111',
            'alamat' => 'Alamat Baru',
            'is_aktif' => '1',
            'email' => 'pegawai.baru@test.local',
            'role_id' => $rolePegawai->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $storePegawaiResponse->assertRedirect(route('admin.pegawai.index'));
        $this->assertDatabaseHas('pegawai', ['nama' => 'Pegawai Baru']);
        $this->assertDatabaseHas('users', ['email' => 'pegawai.baru@test.local']);

        $newPegawai = \App\Models\Pegawai::query()->where('nama', 'Pegawai Baru')->firstOrFail();

        $updatePegawaiResponse = $this->actingAs($admin)->put(route('admin.pegawai.update', $newPegawai), [
            'jenis_pegawai' => 'asn',
            'nip' => '198701012026010001',
            'nama' => 'Pegawai Update',
            'jabatan' => 'Bidan',
            'unit_kerja' => 'KIA',
            'no_hp' => '082222222222',
            'alamat' => 'Alamat Update',
            'is_aktif' => '1',
            'email' => 'pegawai.update@test.local',
            'role_id' => $rolePegawai->id,
            'password' => '',
            'password_confirmation' => '',
        ]);
        $updatePegawaiResponse->assertRedirect(route('admin.pegawai.index'));
        $this->assertDatabaseHas('pegawai', ['id' => $newPegawai->id, 'nama' => 'Pegawai Update', 'jabatan' => 'Bidan']);
        $this->assertDatabaseHas('users', ['pegawai_id' => $newPegawai->id, 'email' => 'pegawai.update@test.local']);

        $deletePegawaiResponse = $this->actingAs($admin)->delete(route('admin.pegawai.destroy', $newPegawai));
        $deletePegawaiResponse->assertRedirect(route('admin.pegawai.index'));
        $this->assertDatabaseMissing('pegawai', ['id' => $newPegawai->id]);

        $monitoringLaporanResponse = $this->actingAs($admin)->get(route('admin.monitoring-laporan'));
        $monitoringLaporanResponse->assertOk();
        $monitoringLaporanResponse->assertViewHas('reports', function ($reports) use ($laporan) {
            return $reports->getCollection()->contains('id', $laporan->id);
        });
    }
}
