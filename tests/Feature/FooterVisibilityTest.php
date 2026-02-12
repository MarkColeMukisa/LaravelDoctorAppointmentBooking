<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_is_hidden_on_auth_pages(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Support: support@example.com');

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('Support: support@example.com');
    }

    public function test_footer_is_hidden_on_dashboard_pages(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_PATIENT])->save();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Support: support@example.com');
    }

    public function test_footer_is_visible_on_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Support: support@example.com');
    }
}
