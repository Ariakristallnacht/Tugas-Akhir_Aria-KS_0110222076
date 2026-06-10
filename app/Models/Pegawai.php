<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';

    protected $fillable = [
        'nip',
        'jenis_pegawai',
        'nama',
        'jabatan',
        'unit_kerja',
        'no_hp',
        'alamat',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $pegawai): void {
            $pegawai->laporanKegiatan()->get()->each->delete();
            $pegawai->pengajuanDinas()->get()->each->delete();
            $pegawai->monitoring()->delete();
            $pegawai->jadwalPegawai()->delete();
            $pegawai->user?->delete();
        });
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function jadwalPegawai(): HasMany
    {
        return $this->hasMany(JadwalPegawai::class);
    }

    public function jadwal(): BelongsToMany
    {
        return $this->belongsToMany(Jadwal::class, 'jadwal_pegawai')
            ->withPivot(['peran_tugas', 'status_penugasan'])
            ->withTimestamps();
    }

    public function pengajuanDinas(): HasMany
    {
        return $this->hasMany(PengajuanDinas::class);
    }

    public function monitoring(): HasMany
    {
        return $this->hasMany(Monitoring::class);
    }

    public function laporanKegiatan(): HasMany
    {
        return $this->hasMany(LaporanKegiatan::class);
    }

    public function scopeSelectable(Builder $query): Builder
    {
        return $query
            ->where('is_aktif', true)
            ->where(function (Builder $nested): void {
                $nested
                    ->whereDoesntHave('user')
                    ->orWhereHas('user.role', fn (Builder $roleQuery) => $roleQuery->whereNotIn('kode', ['admin', 'pj_penjadwalan']));
            });
    }
}
