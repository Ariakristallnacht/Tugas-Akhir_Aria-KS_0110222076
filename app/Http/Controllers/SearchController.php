<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\LaporanKegiatan;
use App\Models\Pegawai;
use App\Models\PengajuanDinas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $user = $request->user();
        $roleCode = $user?->roleKode();
        $pegawai = $user?->pegawai;

        $results = [
            'pegawai' => collect(),
            'jadwal' => collect(),
            'laporan' => collect(),
            'pengajuan' => collect(),
        ];

        if ($query !== '') {
            if ($roleCode === 'admin') {
                $results['pegawai'] = Pegawai::query()
                    ->where(function (Builder $builder) use ($query) {
                        $builder
                            ->where('nama', 'like', "%{$query}%")
                            ->orWhere('nip', 'like', "%{$query}%")
                            ->orWhere('jabatan', 'like', "%{$query}%")
                            ->orWhere('unit_kerja', 'like', "%{$query}%");
                    })
                    ->latest('nama')
                    ->limit(8)
                    ->get();

                $results['jadwal'] = Jadwal::query()
                    ->with(['kegiatan', 'pegawai'])
                    ->where(function (Builder $builder) use ($query) {
                        $builder
                            ->where('lokasi', 'like', "%{$query}%")
                            ->orWhere('keterangan', 'like', "%{$query}%")
                            ->orWhereHas('kegiatan', fn (Builder $subQuery) => $subQuery->where('nama_kegiatan', 'like', "%{$query}%"))
                            ->orWhereHas('pegawai', fn (Builder $subQuery) => $subQuery->where('nama', 'like', "%{$query}%"));
                    })
                    ->latest('tanggal')
                    ->limit(8)
                    ->get();

                $results['laporan'] = LaporanKegiatan::query()
                    ->with(['pegawai', 'jadwal.kegiatan'])
                    ->where(function (Builder $builder) use ($query) {
                        $builder
                            ->where('laporan', 'like', "%{$query}%")
                            ->orWhereHas('pegawai', fn (Builder $subQuery) => $subQuery->where('nama', 'like', "%{$query}%"))
                            ->orWhereHas('jadwal.kegiatan', fn (Builder $subQuery) => $subQuery->where('nama_kegiatan', 'like', "%{$query}%"))
                            ->orWhereHas('jadwal', fn (Builder $subQuery) => $subQuery
                                ->where('lokasi', 'like', "%{$query}%")
                                ->orWhere('keterangan', 'like', "%{$query}%"));
                    })
                    ->latest('tanggal')
                    ->limit(8)
                    ->get();
            } elseif ($roleCode === 'pj_penjadwalan') {
                $results['jadwal'] = Jadwal::query()
                    ->with(['kegiatan', 'pegawai'])
                    ->where(function (Builder $builder) use ($query) {
                        $builder
                            ->where('lokasi', 'like', "%{$query}%")
                            ->orWhere('keterangan', 'like', "%{$query}%")
                            ->orWhereHas('kegiatan', fn (Builder $subQuery) => $subQuery->where('nama_kegiatan', 'like', "%{$query}%"))
                            ->orWhereHas('pegawai', fn (Builder $subQuery) => $subQuery->where('nama', 'like', "%{$query}%"));
                    })
                    ->latest('tanggal')
                    ->limit(8)
                    ->get();

                $results['laporan'] = LaporanKegiatan::query()
                    ->with(['pegawai', 'jadwal.kegiatan'])
                    ->where(function (Builder $builder) use ($query) {
                        $builder
                            ->where('laporan', 'like', "%{$query}%")
                            ->orWhereHas('pegawai', fn (Builder $subQuery) => $subQuery->where('nama', 'like', "%{$query}%"))
                            ->orWhereHas('jadwal.kegiatan', fn (Builder $subQuery) => $subQuery->where('nama_kegiatan', 'like', "%{$query}%"))
                            ->orWhereHas('jadwal', fn (Builder $subQuery) => $subQuery
                                ->where('lokasi', 'like', "%{$query}%")
                                ->orWhere('keterangan', 'like', "%{$query}%"));
                    })
                    ->latest('tanggal')
                    ->limit(8)
                    ->get();

                $results['pengajuan'] = PengajuanDinas::query()
                    ->with('pegawai')
                    ->where(function (Builder $builder) use ($query) {
                        $builder
                            ->where('tujuan', 'like', "%{$query}%")
                            ->orWhere('kegiatan', 'like', "%{$query}%")
                            ->orWhere('keterangan', 'like', "%{$query}%")
                            ->orWhereHas('pegawai', fn (Builder $subQuery) => $subQuery->where('nama', 'like', "%{$query}%"));
                    })
                    ->latest('tanggal_pengajuan')
                    ->limit(8)
                    ->get();
            } elseif ($roleCode === 'pegawai' && $pegawai) {
                $results['jadwal'] = Jadwal::query()
                    ->with(['kegiatan', 'pegawai'])
                    ->whereHas('pegawai', fn (Builder $builder) => $builder->where('pegawai.id', $pegawai->id))
                    ->where(function (Builder $builder) use ($query) {
                        $builder
                            ->where('lokasi', 'like', "%{$query}%")
                            ->orWhere('keterangan', 'like', "%{$query}%")
                            ->orWhereHas('kegiatan', fn (Builder $subQuery) => $subQuery->where('nama_kegiatan', 'like', "%{$query}%"));
                    })
                    ->latest('tanggal')
                    ->limit(8)
                    ->get();

                $results['pengajuan'] = PengajuanDinas::query()
                    ->where('pegawai_id', $pegawai->id)
                    ->where(function (Builder $builder) use ($query) {
                        $builder
                            ->where('tujuan', 'like', "%{$query}%")
                            ->orWhere('kegiatan', 'like', "%{$query}%")
                            ->orWhere('keterangan', 'like', "%{$query}%");
                    })
                    ->latest('tanggal_pengajuan')
                    ->limit(8)
                    ->get();
            }
        }

        return view('search.index', [
            'query' => $query,
            'results' => $results,
            'roleCode' => $roleCode,
        ]);
    }
}
