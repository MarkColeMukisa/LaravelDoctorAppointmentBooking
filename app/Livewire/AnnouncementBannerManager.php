<?php

namespace App\Livewire;

use App\Models\AnnouncementBanner;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AnnouncementBannerManager extends Component
{
    public ?int $editingBannerId = null;

    public string $eyebrow = 'New in AmSam Clinic:';

    public string $message = '';

    public string $linkUrl = '';

    public string $linkLabel = 'Learn more';

    public string $imageUrl = '';

    public bool $isActive = true;

    public function save(): void
    {
        if (! $this->authorizedAdmin()) {
            return;
        }

        $validated = $this->validate([
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:255'],
            'linkUrl' => ['nullable', 'url', 'max:255'],
            'linkLabel' => ['nullable', 'string', 'max:60'],
            'imageUrl' => ['nullable', 'url', 'max:255'],
            'isActive' => ['required', 'boolean'],
        ]);

        $payload = [
            'eyebrow' => $validated['eyebrow'] !== '' ? $validated['eyebrow'] : null,
            'message' => $validated['message'],
            'link_url' => $validated['linkUrl'] !== '' ? $validated['linkUrl'] : null,
            'link_label' => $validated['linkLabel'] !== '' ? $validated['linkLabel'] : null,
            'image_url' => $validated['imageUrl'] !== '' ? $validated['imageUrl'] : null,
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingBannerId) {
            $banner = AnnouncementBanner::query()->find($this->editingBannerId);
            if (! $banner) {
                $this->resetForm();

                return;
            }

            $banner->update($payload);
            session()->flash('message', 'Announcement banner updated.');
            $this->resetForm();

            return;
        }

        $nextSortOrder = (int) AnnouncementBanner::query()->max('sort_order') + 1;

        AnnouncementBanner::query()->create([
            ...$payload,
            'sort_order' => $nextSortOrder,
        ]);

        session()->flash('message', 'Announcement banner created.');
        $this->resetForm();
    }

    public function edit(int $bannerId): void
    {
        if (! $this->authorizedAdmin()) {
            return;
        }

        $banner = AnnouncementBanner::query()->find($bannerId);
        if (! $banner) {
            return;
        }

        $this->editingBannerId = $banner->id;
        $this->eyebrow = $banner->eyebrow ?? '';
        $this->message = $banner->message;
        $this->linkUrl = $banner->link_url ?? '';
        $this->linkLabel = $banner->link_label ?? 'Learn more';
        $this->imageUrl = $banner->image_url ?? '';
        $this->isActive = $banner->is_active;
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        if (! $this->authorizedAdmin()) {
            return;
        }

        $this->resetForm();
    }

    public function toggleActive(int $bannerId): void
    {
        if (! $this->authorizedAdmin()) {
            return;
        }

        $banner = AnnouncementBanner::query()->find($bannerId);
        if (! $banner) {
            return;
        }

        $banner->is_active = ! $banner->is_active;
        $banner->save();
    }

    public function moveUp(int $bannerId): void
    {
        if (! $this->authorizedAdmin()) {
            return;
        }

        $currentBanner = AnnouncementBanner::query()->find($bannerId);
        if (! $currentBanner) {
            return;
        }

        $previousBanner = AnnouncementBanner::query()
            ->where('sort_order', '<', $currentBanner->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if (! $previousBanner) {
            return;
        }

        $this->swapSortOrder($currentBanner, $previousBanner);
    }

    public function moveDown(int $bannerId): void
    {
        if (! $this->authorizedAdmin()) {
            return;
        }

        $currentBanner = AnnouncementBanner::query()->find($bannerId);
        if (! $currentBanner) {
            return;
        }

        $nextBanner = AnnouncementBanner::query()
            ->where('sort_order', '>', $currentBanner->sort_order)
            ->orderBy('sort_order')
            ->first();

        if (! $nextBanner) {
            return;
        }

        $this->swapSortOrder($currentBanner, $nextBanner);
    }

    public function archive(int $bannerId): void
    {
        if (! $this->authorizedAdmin()) {
            return;
        }

        $banner = AnnouncementBanner::query()->find($bannerId);
        if (! $banner) {
            return;
        }

        $banner->delete();
        $this->normalizeSortOrder();
    }

    public function restore(int $bannerId): void
    {
        if (! $this->authorizedAdmin()) {
            return;
        }

        $banner = AnnouncementBanner::query()->onlyTrashed()->find($bannerId);
        if (! $banner) {
            return;
        }

        $nextSortOrder = (int) AnnouncementBanner::query()->max('sort_order') + 1;
        $banner->restore();
        $banner->sort_order = $nextSortOrder;
        $banner->save();
    }

    public function deletePermanently(int $bannerId): void
    {
        if (! $this->authorizedSuperAdmin()) {
            $this->addError('permission', 'Only super-admin can permanently delete banners.');

            return;
        }

        $banner = AnnouncementBanner::query()->onlyTrashed()->find($bannerId);
        if (! $banner) {
            return;
        }

        $banner->forceDelete();
    }

    public function render(): View
    {
        return view('livewire.announcement-banner-manager', [
            'announcementBanners' => AnnouncementBanner::query()
                ->orderBy('sort_order')
                ->orderByDesc('updated_at')
                ->get(),
            'archivedAnnouncementBanners' => AnnouncementBanner::query()
                ->onlyTrashed()
                ->orderByDesc('deleted_at')
                ->get(),
            'canDeletePermanently' => $this->authorizedSuperAdmin(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingBannerId = null;
        $this->eyebrow = 'New in AmSam Clinic:';
        $this->message = '';
        $this->linkUrl = '';
        $this->linkLabel = 'Learn more';
        $this->imageUrl = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    private function swapSortOrder(AnnouncementBanner $firstBanner, AnnouncementBanner $secondBanner): void
    {
        DB::transaction(function () use ($firstBanner, $secondBanner): void {
            $temporarySortOrder = $firstBanner->sort_order;
            $firstBanner->sort_order = $secondBanner->sort_order;
            $secondBanner->sort_order = $temporarySortOrder;
            $firstBanner->save();
            $secondBanner->save();
        });
    }

    private function normalizeSortOrder(): void
    {
        $orderedBanners = AnnouncementBanner::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id']);

        foreach ($orderedBanners as $index => $banner) {
            AnnouncementBanner::query()
                ->where('id', $banner->id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    private function authorizedAdmin(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->role === User::ROLE_ADMIN;
    }

    private function authorizedSuperAdmin(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
