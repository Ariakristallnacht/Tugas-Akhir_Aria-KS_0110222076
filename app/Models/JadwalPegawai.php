<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPegawai extends Model
{
    use HasFactory;

    protected $table = 'jadwal_pegawai';

    protected $fillable = [
        'jadwal_id',
        'pegawai_id',
        'peran_tugas',
        'status_penugasan',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
