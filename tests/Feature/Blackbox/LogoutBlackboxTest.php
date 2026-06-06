<?php

namespace Tests\Feature\Blackbox;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Support\InteractsWithBlackboxData;
use Tests\TestCase;

class LogoutBlackboxTest extends TestCase
{
    use InteractsWithBlackboxData;
    use RefreshDatabase;

    public function test_all_users_can_logout_and_are_redirected_to_login(): void
    {
        $admin = $this->createUserWithRole('admin');
        $pj = $this->createUserWithRole('pj_penjadwalan');
        $pegawai = $this->createUserWithRole('pegawai');

        foreach ([$admin, $pj, $pegawai] as $user) {
            $response = $this->actingAs($user)->post(route('logout'));

            $response->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }
}
