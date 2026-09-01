<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login page.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated user can access dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $employeeRole = Role::create(['name' => 'employee']);
        $user = User::factory()->create();
        $user->roles()->attach($employeeRole);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
