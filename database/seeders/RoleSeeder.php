<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('roles')->upsert([
            [
                'kode' => 'admin',
                'nama' => 'Admin',
                'deskripsi' => 'Mengelola akun, data pegawai, monitoring kegiatan, dan laporan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'pj_penjadwalan',
                'nama' => 'PJ Penjadwalan',
                'deskripsi' => 'Memverifikasi dinas luar, menyusun jadwal, dan membuat laporan kegiatan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'pegawai',
                'nama' => 'Pegawai',
                'deskripsi' => 'Melihat jadwal dan mengajukan dinas luar.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['kode'], ['nama', 'deskripsi', 'updated_at']);
    }
}
