<?php

namespace App\View\Components;

use App\Models\AnnouncementBanner as AnnouncementBannerModel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class AnnouncementBanner extends Component
{
    public Collection $banners;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->banners = AnnouncementBannerModel::activeForDisplay();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.announcement-banner');
    }
}
