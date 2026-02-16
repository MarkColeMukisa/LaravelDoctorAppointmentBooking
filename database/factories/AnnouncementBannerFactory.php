<?php

namespace Database\Factories;

use App\Models\AnnouncementBanner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnnouncementBanner>
 */
class AnnouncementBannerFactory extends Factory
{
    protected $model = AnnouncementBanner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eyebrow' => 'New in AmSam Clinic:',
            'message' => fake()->sentence(6),
            'link_url' => fake()->optional()->url(),
            'link_label' => 'Learn more',
            'image_url' => fake()->optional()->imageUrl(80, 80, 'health'),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
