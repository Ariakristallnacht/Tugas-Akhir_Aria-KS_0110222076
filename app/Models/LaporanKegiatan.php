<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LaporanKegiatan extends Model
{
    use HasFactory;

    protected $table = 'laporan_kegiatan';

    protected $fillable = [
        'jadwal_id',
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

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
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
