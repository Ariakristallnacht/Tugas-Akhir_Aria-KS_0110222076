<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PengajuanDinas extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_dinas';

    protected $fillable = [
        'pegawai_id',
        'tanggal_pengajuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tujuan',
        'kegiatan',
        'keterangan',
        'bukti_surat_path',
        'bukti_surat_nama',
        'bukti_surat_mime',
        'status',
        'diverifikasi_oleh',
        'diverifikasi_at',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'diverifikasi_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $pengajuanDinas): void {
            if ($pengajuanDinas->bukti_surat_path) {
                Storage::disk('public')->delete($pengajuanDinas->bukti_surat_path);
            }
        });
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function laporanKegiatan(): HasMany
    {
        return $this->hasMany(LaporanKegiatan::class);
    }

    public function getBuktiSuratUrlAttribute(): ?string
    {
        if (! $this->bukti_surat_path) {
            return null;
        }

        return Storage::disk('public')->url($this->bukti_surat_path);
    }

    public function getHasBuktiSuratAttribute(): bool
    {
        return filled($this->bukti_surat_path);
    }

    public function getBuktiSuratIsPdfAttribute(): bool
    {
        return $this->bukti_surat_mime === 'application/pdf';
    }
}
