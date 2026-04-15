<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
