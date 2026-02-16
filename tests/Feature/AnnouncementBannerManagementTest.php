<?php

namespace Tests\Feature;

use App\Livewire\AnnouncementBannerManager;
use App\Models\AnnouncementBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnnouncementBannerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_announcements_page_contains_component(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin-announcements'))
            ->assertOk()
            ->assertSeeLivewire('announcement-banner-manager');
    }

    public function test_admin_can_create_archive_and_restore_announcement_banner(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(AnnouncementBannerManager::class)
            ->set('eyebrow', 'New in AmSam Clinic:')
            ->set('message', 'Email Throttling is live!')
            ->set('linkUrl', 'https://docs.mailtrap.io/email-marketing/campaigns/email-throttling/?utm_source=banner')
            ->set('linkLabel', 'Learn more')
            ->set('imageUrl', 'https://mailtrap.io/wp-content/uploads/2026/01/star-illustration.svg')
            ->set('isActive', true)
            ->call('save');

        $banner = AnnouncementBanner::query()->first();
        $this->assertNotNull($banner);

        Livewire::test(AnnouncementBannerManager::class)
            ->call('archive', $banner->id);

        $this->assertSoftDeleted('announcement_banners', [
            'id' => $banner->id,
        ]);

        Livewire::test(AnnouncementBannerManager::class)
            ->call('restore', $banner->id);

        $this->assertDatabaseHas('announcement_banners', [
            'id' => $banner->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_reorder_announcement_banners(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $firstBanner = AnnouncementBanner::factory()->create([
            'message' => 'First banner',
            'sort_order' => 1,
        ]);

        $secondBanner = AnnouncementBanner::factory()->create([
            'message' => 'Second banner',
            'sort_order' => 2,
        ]);

        $this->actingAs($admin);

        Livewire::test(AnnouncementBannerManager::class)
            ->call('moveUp', $secondBanner->id);

        $this->assertDatabaseHas('announcement_banners', [
            'id' => $secondBanner->id,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('announcement_banners', [
            'id' => $firstBanner->id,
            'sort_order' => 2,
        ]);
    }

    public function test_non_super_admin_cannot_permanently_delete_archived_banner(): void
    {
        config()->set('app.super_admin_emails', ['super@clinic.test']);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin@clinic.test',
        ]);

        $banner = AnnouncementBanner::factory()->create([
            'message' => 'Archive only banner',
            'sort_order' => 1,
        ]);
        $banner->delete();

        $this->actingAs($admin);

        Livewire::test(AnnouncementBannerManager::class)
            ->call('deletePermanently', $banner->id)
            ->assertHasErrors(['permission']);

        $this->assertSoftDeleted('announcement_banners', [
            'id' => $banner->id,
        ]);
    }

    public function test_super_admin_can_permanently_delete_archived_banner(): void
    {
        config()->set('app.super_admin_emails', ['super@clinic.test']);

        $superAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'super@clinic.test',
        ]);

        $banner = AnnouncementBanner::factory()->create([
            'message' => 'Delete me permanently',
            'sort_order' => 1,
        ]);
        $banner->delete();

        $this->actingAs($superAdmin);

        Livewire::test(AnnouncementBannerManager::class)
            ->call('deletePermanently', $banner->id);

        $this->assertDatabaseMissing('announcement_banners', [
            'id' => $banner->id,
        ]);
    }
}
