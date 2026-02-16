<?php

namespace Database\Seeders;

use App\Models\AnnouncementBanner;
use Illuminate\Database\Seeder;

class AnnouncementBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (AnnouncementBanner::query()->count() > 0) {
            return;
        }

        AnnouncementBanner::query()->create([
            'eyebrow' => 'New in AmSam Clinic:',
            'message' => 'Email Throttling is live!',
            'link_url' => 'https://docs.mailtrap.io/email-marketing/campaigns/email-throttling/?utm_source=banner',
            'link_label' => 'Learn more',
            'image_url' => 'https://mailtrap.io/wp-content/uploads/2026/01/star-illustration.svg',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}
