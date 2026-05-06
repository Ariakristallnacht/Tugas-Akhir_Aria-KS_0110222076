<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultRoleAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'role_kode' => 'admin',
                'pegawai_nama' => 'Nur Aisyah Pratama',
                'email' => 'admin@puskesmasbunar.id',
            ],
            [
                'role_kode' => 'pj_penjadwalan',
                'pegawai_nama' => 'Rina Fitriani',
                'email' => 'pj@puskesmasbunar.id',
            ],
            [
                'role_kode' => 'pegawai',
                'pegawai_nama' => 'Dewi Lestari',
                'email' => 'pegawai@puskesmasbunar.id',
            ],
        ];

        foreach ($accounts as $account) {
            $role = Role::query()->where('kode', $account['role_kode'])->first();
            $pegawai = Pegawai::query()->where('nama', $account['pegawai_nama'])->first();

            if (! $role || ! $pegawai) {
                continue;
            }

            User::query()->updateOrCreate(
                ['pegawai_id' => $pegawai->id],
                [
                    'name' => $pegawai->nama,
                    'email' => $account['email'],
                    'role_id' => $role->id,
                    'password' => 'password',
                ]
            );
        }
    }
}
