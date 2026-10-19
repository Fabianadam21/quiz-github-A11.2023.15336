<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_pages_are_available(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_each_role_can_open_its_dashboard(): void
    {
        foreach ([
            'admin' => '/admin/dashboard',
            'dokter' => '/dokter/dashboard',
            'pasien' => '/pasien/dashboard',
        ] as $role => $path) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get($path)
                ->assertOk()
                ->assertSee('Dashboard '.ucfirst($role));
        }
    }

    public function test_login_redirects_users_to_their_role_dashboard(): void
    {
        foreach ([
            'admin' => '/admin/dashboard',
            'dokter' => '/dokter/dashboard',
            'pasien' => '/pasien/dashboard',
        ] as $role => $path) {
            $user = User::factory()->create(['role' => $role]);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect($path);
        }
    }
}
