<?php

namespace Tests\Feature;

use App\Models\AnnouncementBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementBannerDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_announcement_banner_is_visible_on_home_page_only(): void
    {
        AnnouncementBanner::factory()->create([
            'eyebrow' => 'New in AmSam Clinic:',
            'message' => 'Email Throttling is live!',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('New in AmSam Clinic:')
            ->assertSee('Email Throttling is live!');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertDontSee('Email Throttling is live!');
    }

    public function test_banner_is_hidden_on_login_and_register_pages(): void
    {
        AnnouncementBanner::factory()->create([
            'message' => 'Hidden on auth pages',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Hidden on auth pages');

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('Hidden on auth pages');
    }

    public function test_inactive_and_archived_banners_are_not_visible_on_home_page(): void
    {
        AnnouncementBanner::factory()->create([
            'message' => 'Visible banner',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        AnnouncementBanner::factory()->create([
            'message' => 'Inactive banner',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $archivedBanner = AnnouncementBanner::factory()->create([
            'message' => 'Archived banner',
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $archivedBanner->delete();

        $this->get('/')
            ->assertOk()
            ->assertSee('Visible banner')
            ->assertDontSee('Inactive banner')
            ->assertDontSee('Archived banner');
    }

    public function test_home_banner_contains_auto_rotation_script_when_multiple_active_banners_exist(): void
    {
        AnnouncementBanner::factory()->create([
            'message' => 'First banner',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        AnnouncementBanner::factory()->create([
            'message' => 'Second banner',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('nextBanner')
            ->assertSee('rotationMs');
    }
}
