<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LaporanKegiatan extends Model
{
    use HasFactory;

    public const JENIS_LAYANAN = 'layanan';
    public const JENIS_DINAS_LUAR = 'dinas_luar';

    protected $table = 'laporan_kegiatan';

    protected $fillable = [
        'jenis_kegiatan',
        'jadwal_id',
        'pengajuan_dinas_id',
        'pegawai_id',
        'tanggal',
        'laporan',
        'dokumen_laporan_path',
        'dokumen_laporan_nama',
        'dokumen_laporan_mime',
        'status_verifikasi',
        'diverifikasi_oleh',
        'diverifikasi_at',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'diverifikasi_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $laporanKegiatan): void {
            if ($laporanKegiatan->dokumen_laporan_path) {
                Storage::disk('public')->delete($laporanKegiatan->dokumen_laporan_path);
            }
        });
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function pengajuanDinas(): BelongsTo
    {
        return $this->belongsTo(PengajuanDinas::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function getJenisKegiatanLabelAttribute(): string
    {
        return $this->jenis_kegiatan === self::JENIS_DINAS_LUAR ? 'Dinas Luar' : 'Layanan';
    }

    public function getKegiatanNamaAttribute(): string
    {
        if ($this->jenis_kegiatan === self::JENIS_DINAS_LUAR) {
            return $this->pengajuanDinas?->kegiatan ?? 'Kegiatan dinas luar tidak ditemukan';
        }

        return $this->jadwal?->kegiatan?->nama_kegiatan ?? 'Kegiatan layanan tidak ditemukan';
    }

    public function getLokasiKegiatanAttribute(): string
    {
        if ($this->jenis_kegiatan === self::JENIS_DINAS_LUAR) {
            return $this->pengajuanDinas?->tujuan ?? 'Tujuan belum diisi';
        }

        return $this->jadwal?->lokasi ?? 'Lokasi belum diisi';
    }

    public function getWaktuKegiatanAttribute(): string
    {
        if ($this->jenis_kegiatan === self::JENIS_DINAS_LUAR) {
            $startDate = $this->pengajuanDinas?->tanggal_mulai;
            $endDate = $this->pengajuanDinas?->tanggal_selesai;

            if (! $startDate || ! $endDate) {
                return '-';
            }

            if ($startDate->isSameDay($endDate)) {
                return $startDate->translatedFormat('d F Y');
            }

            return $startDate->translatedFormat('d F Y').' s.d. '.$endDate->translatedFormat('d F Y');
        }

        $startTime = $this->jadwal?->waktu_mulai?->format('H:i');
        $endTime = $this->jadwal?->waktu_selesai?->format('H:i');

        if (! $startTime && ! $endTime) {
            return '-';
        }

        return trim(($startTime ?? '-').' - '.($endTime ?? '-'));
    }

    public function getStatusReferensiAttribute(): string
    {
        if ($this->jenis_kegiatan === self::JENIS_DINAS_LUAR) {
            return ucfirst($this->pengajuanDinas?->status ?? 'tidak diketahui');
        }

        return ucfirst($this->jadwal?->status ?? 'tidak diketahui');
    }

    public function getDokumenLaporanUrlAttribute(): ?string
    {
        if (! $this->dokumen_laporan_path) {
            return null;
        }

        return Storage::disk('public')->url($this->dokumen_laporan_path);
    }

    public function getHasDokumenLaporanAttribute(): bool
    {
        return filled($this->dokumen_laporan_path);
    }

    public function getDokumenLaporanIsPdfAttribute(): bool
    {
        return $this->dokumen_laporan_mime === 'application/pdf';
    }
}
