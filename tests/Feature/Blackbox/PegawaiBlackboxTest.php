<?php

namespace Tests\Feature\Blackbox;

use App\Models\LaporanKegiatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Support\InteractsWithBlackboxData;
use Tests\TestCase;

class PegawaiBlackboxTest extends TestCase
{
    use InteractsWithBlackboxData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeToday();
    }

    public function test_pegawai_blackbox_scenarios(): void
    {
        $pj = $this->createUserWithRole('pj_penjadwalan', ['nama' => 'Rina PJ']);
        $pegawai = $this->createUserWithRole('pegawai', ['nama' => 'Dewi Pegawai'], ['email' => 'pegawai@test.local']);

        $kegiatan = $this->createKegiatan(['nama_kegiatan' => 'Pelayanan Gigi']);
        $jadwal = $this->createJadwal($pj, $kegiatan, [
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Poli Gigi',
            'status' => 'terjadwal',
        ], [
            [
                'pegawai' => $pegawai->pegawai,
                'status_penugasan' => 'dijadwalkan',
                'peran_tugas' => 'Operator',
            ],
        ]);

        $pengajuanDisetujui = $this->createPengajuanDinas($pegawai->pegawai, [
            'tanggal_mulai' => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->subDay()->toDateString(),
            'status' => 'disetujui',
            'kegiatan' => 'Pelatihan Lapangan',
        ]);

        $pengajuanDiajukan = $this->createPengajuanDinas($pegawai->pegawai, [
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'diajukan',
            'kegiatan' => 'Rapat Koordinasi',
        ]);

        $loginResponse = $this->post(route('login.store'), [
            'email' => $pegawai->email,
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect('/pegawai');

        $dashboardResponse = $this->actingAs($pegawai)->get(route('pegawai.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertViewHas('submissionCount', 2);
        $dashboardResponse->assertViewHas('pendingSubmissionCount', 1);
        $dashboardResponse->assertViewHas('approvedSubmissionCount', 1);

        $jadwalIndexResponse = $this->actingAs($pegawai)->get(route('pegawai.jadwal-kegiatan', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));
        $jadwalIndexResponse->assertOk();
        $jadwalIndexResponse->assertSeeText('Layanan: 1');

        $jadwalCalendarResponse = $this->actingAs($pegawai)->get(route('pegawai.jadwal-kegiatan', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));
        $jadwalCalendarResponse->assertOk();
        $jadwalCalendarResponse->assertViewHas('activePegawaiForModal', function (array $pegawaiModal) use ($pegawai) {
            return collect($pegawaiModal)->contains(fn (array $item) => $item['id'] === $pegawai->pegawai->id);
        });

        $pdfResponse = $this->actingAs($pegawai)->get(route('jadwal-kegiatan.export-global', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type', ''));

        $storePengajuanResponse = $this->actingAs($pegawai)->post(route('pegawai.pengajuan-dinas.store'), [
            'tanggal_mulai' => now()->addDays(3)->toDateString(),
            'tanggal_selesai' => now()->addDays(4)->toDateString(),
            'tujuan' => 'RS Kabupaten',
            'kegiatan' => 'Rujukan Pasien',
            'keterangan' => 'Perlu koordinasi lanjutan',
            'bukti_surat' => $this->fakePdfUpload('bukti-pengajuan.pdf'),
        ]);
        $storePengajuanResponse->assertRedirect(route('pegawai.pengajuan-dinas.index'));
        $this->assertDatabaseHas('pengajuan_dinas', [
            'tujuan' => 'RS Kabupaten',
            'status' => 'diajukan',
        ]);

        $pengajuanBaru = \App\Models\PengajuanDinas::query()->where('tujuan', 'RS Kabupaten')->firstOrFail();

        $updatePengajuanResponse = $this->actingAs($pegawai)->put(route('pegawai.pengajuan-dinas.update', $pengajuanBaru), [
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(6)->toDateString(),
            'tujuan' => 'RS Kabupaten Update',
            'kegiatan' => 'Rujukan Pasien Update',
            'keterangan' => 'Jadwal diperbarui',
            'alasan_perubahan_tanggal' => 'Menyesuaikan undangan resmi.',
        ]);
        $updatePengajuanResponse->assertRedirect(route('pegawai.pengajuan-dinas.index'));
        $this->assertDatabaseHas('pengajuan_dinas', [
            'id' => $pengajuanBaru->id,
            'tujuan' => 'RS Kabupaten Update',
            'status' => 'diajukan',
        ]);

        $deletePengajuanResponse = $this->actingAs($pegawai)->delete(route('pegawai.pengajuan-dinas.destroy', $pengajuanBaru));
        $deletePengajuanResponse->assertRedirect(route('pegawai.pengajuan-dinas.index'));
        $this->assertDatabaseMissing('pengajuan_dinas', ['id' => $pengajuanBaru->id]);

        $laporanFormResponse = $this->actingAs($pegawai)->get(route('pegawai.laporan-kegiatan.create'));
        $laporanFormResponse->assertOk();
        $laporanFormResponse->assertViewHas('pengajuanDinasOptions', function ($pengajuanOptions) use ($pengajuanDisetujui, $pengajuanDiajukan) {
            return $pengajuanOptions->contains('id', $pengajuanDisetujui->id)
                && ! $pengajuanOptions->contains('id', $pengajuanDiajukan->id);
        });

        $storeLaporanLayananResponse = $this->actingAs($pegawai)->post(route('pegawai.laporan-kegiatan.store'), [
            'jenis_kegiatan' => LaporanKegiatan::JENIS_LAYANAN,
            'jadwal_id' => $jadwal->id,
            'pengajuan_dinas_id' => '',
            'tanggal' => now()->toDateString(),
            'laporan' => 'Laporan kegiatan layanan lengkap',
            'dokumen_laporan' => $this->fakePdfUpload('laporan-layanan.pdf'),
        ]);
        $storeLaporanLayananResponse->assertRedirect(route('pegawai.laporan-kegiatan.index'));
        $this->assertDatabaseHas('laporan_kegiatan', [
            'jadwal_id' => $jadwal->id,
            'pegawai_id' => $pegawai->pegawai->id,
            'jenis_kegiatan' => LaporanKegiatan::JENIS_LAYANAN,
        ]);

        $storeLaporanDinasResponse = $this->actingAs($pegawai)->post(route('pegawai.laporan-kegiatan.store'), [
            'jenis_kegiatan' => LaporanKegiatan::JENIS_DINAS_LUAR,
            'jadwal_id' => '',
            'pengajuan_dinas_id' => $pengajuanDisetujui->id,
            'tanggal' => now()->toDateString(),
            'laporan' => 'Laporan kegiatan dinas luar lengkap',
            'dokumen_laporan' => $this->fakePdfUpload('laporan-dinas.pdf'),
        ]);
        $storeLaporanDinasResponse->assertRedirect(route('pegawai.laporan-kegiatan.index'));
        $this->assertDatabaseHas('laporan_kegiatan', [
            'pengajuan_dinas_id' => $pengajuanDisetujui->id,
            'pegawai_id' => $pegawai->pegawai->id,
            'jenis_kegiatan' => LaporanKegiatan::JENIS_DINAS_LUAR,
        ]);
    }
}
