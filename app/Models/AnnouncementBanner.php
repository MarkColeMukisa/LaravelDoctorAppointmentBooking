<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class AnnouncementBanner extends Model
{
    /** @use HasFactory<\Database\Factories\AnnouncementBannerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'eyebrow',
        'message',
        'link_url',
        'link_label',
        'image_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function activeForDisplay(): Collection
    {
        return self::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->get([
                'id',
                'eyebrow',
                'message',
                'link_url',
                'link_label',
                'image_url',
            ]);
    }
}
