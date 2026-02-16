<div class="mx-auto max-w-[85rem] px-4 py-5 sm:px-6 lg:px-8 lg:py-7">
    <x-success-toast :livewire-only="true" />
    @if ($errors->has('permission'))
        <div wire:transition.opacity class="mb-3 rounded-lg bg-rose-600 p-3 text-sm text-white" role="alert">
            <span class="font-semibold">Access Denied</span>
            <span class="ml-1">{{ $errors->first('permission') }}</span>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[24rem_1fr]">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100">
                {{ $editingBannerId ? 'Edit Announcement Banner' : 'Create Announcement Banner' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">Shown above navigation and dismissible by users.</p>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div>
                    <label for="eyebrow" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Title</label>
                    <input id="eyebrow" type="text" wire:model="eyebrow" class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="New in AmSam Clinic " />
                    <x-input-error class="mt-2" :messages="$errors->get('eyebrow')" />
                </div>

                <div>
                    <label for="message" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Message</label>
                    <textarea id="message" rows="3" wire:model="message" class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Email Throttling is live!"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('message')" />
                </div>

                <div>
                    <label for="linkUrl" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Link URL</label>
                    <input id="linkUrl" type="url" wire:model="linkUrl" class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://docs.mailtrap.io/..." />
                    <x-input-error class="mt-2" :messages="$errors->get('linkUrl')" />
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="linkLabel" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Link Label</label>
                        <input id="linkLabel" type="text" wire:model="linkLabel" class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Learn more" />
                        <x-input-error class="mt-2" :messages="$errors->get('linkLabel')" />
                    </div>
                    <div>
                        <label for="imageUrl" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Image URL</label>
                        <input id="imageUrl" type="url" wire:model="imageUrl" class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://.../star.svg" />
                        <x-input-error class="mt-2" :messages="$errors->get('imageUrl')" />
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Display Banner</p>
                        <p class="text-xs text-slate-500">Inactive banners are hidden from users.</p>
                    </div>
                    <label class="inline-flex cursor-pointer items-center gap-2">
                        <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm text-slate-700">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3">
                    @if ($editingBannerId)
                        <button type="button" wire:click="cancelEdit" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    @endif
                    <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        {{ $editingBannerId ? 'Update Banner' : 'Create Banner' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-neutral-700">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100">Display Order</h3>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">Top item appears first in the public notification bar.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Order</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Content</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse ($announcementBanners as $banner)
                                <tr wire:key="active-banner-{{ $banner->id }}" class="align-top">
                                    <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">#{{ $banner->sort_order }}</td>
                                    <td class="max-w-xl px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                        @if ($banner->eyebrow)
                                            <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $banner->eyebrow }}</p>
                                        @endif
                                        <p>{{ $banner->message }}</p>
                                        @if ($banner->link_url)
                                            <p class="mt-1 text-xs text-blue-600">{{ $banner->link_label ?: 'Learn more' }} → {{ $banner->link_url }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">
                                        @if ($banner->is_active)
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-800">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-slate-800">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button" wire:click="moveUp({{ $banner->id }})" class="rounded border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">↑</button>
                                            <button type="button" wire:click="moveDown({{ $banner->id }})" class="rounded border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">↓</button>
                                            <button type="button" wire:click="edit({{ $banner->id }})" class="rounded border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100">Edit</button>
                                            <button type="button" wire:click="toggleActive({{ $banner->id }})" class="rounded border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100">{{ $banner->is_active ? 'Deactivate' : 'Activate' }}</button>
                                            <button type="button" wire:click="archive({{ $banner->id }})" wire:confirm="Archive this banner?" class="rounded border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">Archive</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-neutral-400">No banners created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-neutral-700">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100">Archived Banners</h3>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">Restore previously removed announcements or delete permanently.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Message</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Removed At</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse ($archivedAnnouncementBanners as $banner)
                                <tr wire:key="archived-banner-{{ $banner->id }}">
                                    <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">{{ $banner->message }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700 dark:text-neutral-300">{{ $banner->deleted_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button" wire:click="restore({{ $banner->id }})" class="rounded border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">Restore</button>
                                            @if ($canDeletePermanently)
                                                <button type="button" wire:click="deletePermanently({{ $banner->id }})" wire:confirm="Delete this banner permanently?" class="rounded border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">Delete Permanently</button>
                                            @else
                                                <span class="rounded border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">Super-admin only</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-neutral-400">No archived banners.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
