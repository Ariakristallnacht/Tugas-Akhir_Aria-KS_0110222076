<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminRoleId = DB::table('roles')->where('kode', 'admin')->value('id');
        $pjRoleId = DB::table('roles')->where('kode', 'pj_penjadwalan')->value('id');
        $pegawaiRoleId = DB::table('roles')->where('kode', 'pegawai')->value('id');

        $adminPegawai = Pegawai::updateOrCreate(
            ['nip' => '197901012006041001'],
            [
                'nama' => 'Admin Puskesmas',
                'jabatan' => 'Administrator Sistem',
                'unit_kerja' => 'Tata Usaha',
                'no_hp' => '081234567890',
                'alamat' => 'Puskesmas Bunar',
                'is_aktif' => true,
            ]
        );

        $pjPegawai = Pegawai::updateOrCreate(
            ['nip' => '198402102009122001'],
            [
                'nama' => 'Rani Penjadwalan',
                'jabatan' => 'PJ Penjadwalan',
                'unit_kerja' => 'Pelayanan',
                'no_hp' => '081234567891',
                'alamat' => 'Puskesmas Bunar',
                'is_aktif' => true,
            ]
        );

        $pegawai = Pegawai::updateOrCreate(
            ['nip' => '199105052015031002'],
            [
                'nama' => 'Dina Pegawai',
                'jabatan' => 'Perawat',
                'unit_kerja' => 'Poli Umum',
                'no_hp' => '081234567892',
                'alamat' => 'Puskesmas Bunar',
                'is_aktif' => true,
            ]
        );

        User::updateOrCreate([
            'email' => 'admin@pkmbunar.test',
        ], [
            'name' => 'Admin Puskesmas',
            'email' => 'admin@pkmbunar.test',
            'role_id' => $adminRoleId,
            'pegawai_id' => $adminPegawai->id,
            'password' => 'password',
        ]);

        User::updateOrCreate([
            'email' => 'pj@pkmbunar.test',
        ], [
            'name' => 'PJ Penjadwalan',
            'email' => 'pj@pkmbunar.test',
            'role_id' => $pjRoleId,
            'pegawai_id' => $pjPegawai->id,
            'password' => 'password',
        ]);

        User::updateOrCreate([
            'email' => 'pegawai@pkmbunar.test',
        ], [
            'name' => 'Pegawai Puskesmas',
            'email' => 'pegawai@pkmbunar.test',
            'role_id' => $pegawaiRoleId,
            'pegawai_id' => $pegawai->id,
            'password' => 'password',
        ]);
    }
}
