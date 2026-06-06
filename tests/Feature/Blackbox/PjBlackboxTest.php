<?php

namespace Tests\Feature\Blackbox;

use App\Models\Kegiatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Support\InteractsWithBlackboxData;
use Tests\TestCase;

class PjBlackboxTest extends TestCase
{
    use InteractsWithBlackboxData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeToday();
    }

    public function test_pj_blackbox_scenarios(): void
    {
        $pj = $this->createUserWithRole('pj_penjadwalan', ['nama' => 'Rina PJ'], ['email' => 'pj@test.local']);
        $pegawai = $this->createUserWithRole('pegawai', ['nama' => 'Dewi Pegawai']);
        $pegawaiCadangan = $this->createUserWithRole('pegawai', ['nama' => 'Sari Cadangan']);

        $kegiatan = $this->createKegiatan(['nama_kegiatan' => 'Imunisasi Balita']);
        $jadwal = $this->createJadwal($pj, $kegiatan, [
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Aula Puskesmas',
        ], [$pegawai->pegawai]);

        $pengajuan = $this->createPengajuanDinas($pegawai->pegawai, [
            'status' => 'diajukan',
            'kegiatan' => 'Koordinasi Dinas',
        ]);

        $laporan = $this->createLaporanKegiatan($pegawai->pegawai, [
            'jadwal_id' => $jadwal->id,
        ]);

        $loginResponse = $this->post(route('login.store'), [
            'email' => $pj->email,
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect('/pj');

        $dashboardResponse = $this->actingAs($pj)->get(route('pj.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertViewHas('pendingCount', 1);
        $dashboardResponse->assertViewHas('todaySubmissionCount', 1);
        $dashboardResponse->assertViewHas('reportCount', 1);

        $storeJadwalResponse = $this->actingAs($pj)->post(route('pj.jadwal-kegiatan.store'), [
            'kegiatan_id' => $kegiatan->id,
            'tanggal' => now()->addDay()->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
            'lokasi' => 'Posyandu Melati',
            'keterangan' => 'Jadwal baru',
            'status' => 'terjadwal',
            'petugas' => [
                [
                    'pegawai_id' => $pegawaiCadangan->pegawai->id,
                    'peran_tugas' => 'Operator',
                    'status_penugasan' => 'dijadwalkan',
                ],
            ],
        ]);
        $storeJadwalResponse->assertRedirect(route('pj.jadwal-kegiatan.index'));
        $this->assertDatabaseHas('jadwal', ['lokasi' => 'Posyandu Melati']);
        $jadwalBaru = \App\Models\Jadwal::query()->where('lokasi', 'Posyandu Melati')->firstOrFail();
        $this->assertDatabaseHas('jadwal_pegawai', [
            'jadwal_id' => $jadwalBaru->id,
            'pegawai_id' => $pegawaiCadangan->pegawai->id,
            'status_penugasan' => 'dijadwalkan',
        ]);

        $availabilityResponse = $this->actingAs($pj)->getJson(route('pj.jadwal-kegiatan.availability', [
            'date' => now()->toDateString(),
        ]));
        $availabilityResponse->assertOk();
        $availabilityResponse->assertJsonPath('availability_summary.unavailable_count', 1);

        $calendarDetailResponse = $this->actingAs($pj)->get(route('pj.jadwal-kegiatan.index', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));
        $calendarDetailResponse->assertOk();
        $calendarDetailResponse->assertViewHas('items', function ($items) use ($jadwal) {
            $item = $items->firstWhere('key', 'jadwal-'.$jadwal->id);

            return $item !== null && filled($item['edit_url'] ?? null);
        });

        $editPageResponse = $this->actingAs($pj)->get(route('pj.jadwal-kegiatan.edit', $jadwal));
        $editPageResponse->assertOk();
        $editPageResponse->assertViewHas('jadwal', fn ($viewJadwal) => $viewJadwal->is($jadwal));

        $pdfResponse = $this->actingAs($pj)->get(route('jadwal-kegiatan.export-global', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type', ''));

        $verifikasiIndexResponse = $this->actingAs($pj)->get(route('pj.verifikasi-pengajuan-dinas.index'));
        $verifikasiIndexResponse->assertOk();
        $verifikasiIndexResponse->assertViewHas('submissions', function ($submissions) use ($pengajuan) {
            return $submissions->getCollection()->contains('id', $pengajuan->id);
        });

        $approveResponse = $this->actingAs($pj)->patch(route('pj.verifikasi-pengajuan-dinas.update', $pengajuan), [
            'status' => 'disetujui',
            'catatan_verifikasi' => 'Disetujui untuk kegiatan luar kantor.',
        ]);
        $approveResponse->assertRedirect(route('pj.verifikasi-pengajuan-dinas.index'));
        $this->assertDatabaseHas('pengajuan_dinas', ['id' => $pengajuan->id, 'status' => 'disetujui']);

        $pengajuanDitolak = $this->createPengajuanDinas($pegawaiCadangan->pegawai, [
            'kegiatan' => 'Rapat Ditolak',
            'status' => 'diajukan',
        ]);

        $rejectResponse = $this->actingAs($pj)->patch(route('pj.verifikasi-pengajuan-dinas.update', $pengajuanDitolak), [
            'status' => 'ditolak',
            'catatan_verifikasi' => 'Dokumen belum lengkap.',
        ]);
        $rejectResponse->assertRedirect(route('pj.verifikasi-pengajuan-dinas.index'));
        $this->assertDatabaseHas('pengajuan_dinas', [
            'id' => $pengajuanDitolak->id,
            'status' => 'ditolak',
            'catatan_verifikasi' => 'Dokumen belum lengkap.',
        ]);

        $storeKegiatanResponse = $this->actingAs($pj)->post(route('pj.kegiatan.store'), [
            'nama_kegiatan' => 'Layanan KB',
            'deskripsi' => 'Pelayanan KB rutin',
            'is_aktif' => '1',
        ]);
        $storeKegiatanResponse->assertRedirect(route('pj.kegiatan.index'));
        $this->assertDatabaseHas('kegiatan', ['nama_kegiatan' => 'Layanan KB', 'jenis' => 'layanan']);

        $layanan = Kegiatan::query()->where('nama_kegiatan', 'Layanan KB')->firstOrFail();

        $updateKegiatanResponse = $this->actingAs($pj)->put(route('pj.kegiatan.update', $layanan), [
            'nama_kegiatan' => 'Layanan KB Update',
            'deskripsi' => 'Pelayanan KB revisi',
            'is_aktif' => '1',
        ]);
        $updateKegiatanResponse->assertRedirect(route('pj.kegiatan.index'));
        $this->assertDatabaseHas('kegiatan', ['id' => $layanan->id, 'nama_kegiatan' => 'Layanan KB Update']);

        $deleteKegiatanResponse = $this->actingAs($pj)->delete(route('pj.kegiatan.destroy', $layanan));
        $deleteKegiatanResponse->assertRedirect(route('pj.kegiatan.index'));
        $this->assertDatabaseMissing('kegiatan', ['id' => $layanan->id]);

        $monitoringLaporanResponse = $this->actingAs($pj)->get(route('pj.monitoring-laporan'));
        $monitoringLaporanResponse->assertOk();
        $monitoringLaporanResponse->assertViewHas('reports', function ($reports) use ($laporan) {
            return $reports->getCollection()->contains('id', $laporan->id);
        });
    }
}
