<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_navigation_contains_management_links(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->get(route('admin-dashboard'));

        $expectedLinks = [
            'Dashboard' => route('admin-dashboard'),
            'Doctors' => route('admin-doctors'),
            'Doctor Applications' => route('admin-doctor-applications'),
            'Specialities' => route('admin-specialities'),
            'Announcements' => route('admin-announcements'),
            'All Appointments' => route('admin-appointments'),
        ];

        $response->assertOk();

        foreach ($expectedLinks as $label => $href) {
            $response->assertSee($label);
            $response->assertSee('href="'.$href.'"', false);
        }
    }
}
