<?php

namespace Tests\Feature\Auth;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_redirected_to_admin_dashboard_after_login(): void
    {
        $user = $this->createUserWithRole('admin', 'admin@test.local');

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_pj_redirected_to_pj_dashboard_after_login(): void
    {
        $user = $this->createUserWithRole('pj_penjadwalan', 'pj@test.local');

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/pj');
        $this->assertAuthenticatedAs($user);
    }

    public function test_pegawai_redirected_to_pegawai_dashboard_after_login(): void
    {
        $user = $this->createUserWithRole('pegawai', 'pegawai@test.local');

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/pegawai');
        $this->assertAuthenticatedAs($user);
    }

    private function createUserWithRole(string $kodeRole, string $email): User
    {
        $role = Role::create([
            'kode' => $kodeRole,
            'nama' => str($kodeRole)->replace('_', ' ')->title()->toString(),
        ]);

        $pegawai = Pegawai::create([
            'nip' => fake()->unique()->numerify('##################'),
            'nama' => fake()->name(),
            'jabatan' => 'Petugas',
            'unit_kerja' => 'Puskesmas',
            'is_aktif' => true,
        ]);

        return User::create([
            'name' => fake()->name(),
            'email' => $email,
            'role_id' => $role->id,
            'pegawai_id' => $pegawai->id,
            'password' => 'password',
        ]);
    }
}
