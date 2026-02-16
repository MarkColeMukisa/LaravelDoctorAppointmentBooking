@if ($banners->isNotEmpty())
    @once
        <style>
            .custom-banner {
                background: linear-gradient(90deg, #0f172a 0%, #1e293b 55%, #0f172a 100%);
                color: #e2e8f0;
            }

            .custom-banner__inner {
                max-width: 85rem;
                margin: 0 auto;
                padding: 0.5rem 3rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                min-height: 3rem;
                position: relative;
            }

            .custom-banner__content {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                flex-wrap: wrap;
                text-align: center;
            }

            .custom-banner__content-position {
                display: inline-flex;
                align-items: center;
                gap: 0.625rem;
            }

            .custom-banner__image {
                width: 2rem;
                height: 2rem;
                object-fit: contain;
                border-radius: 999px;
            }

            .custom-banner__content-text {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }

            .custom-banner__link {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                font-size: 0.8125rem;
                line-height: 1rem;
                color: #93c5fd;
                font-weight: 600;
                text-decoration: none;
            }

            .custom-banner__link:hover {
                color: #bfdbfe;
            }

            .custom-banner__close {
                width: 2rem;
                height: 2rem;
                border: 1px solid #475569;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #e2e8f0;
                background: transparent;
                cursor: pointer;
                position: absolute;
                right: 1rem;
                top: 50%;
                transform: translateY(-50%);
            }

            .custom-banner__close:hover {
                background: #1e293b;
            }
        </style>
    @endonce

    <div
        x-data="announcementBanner(@js($banners->values()))"
        x-show="currentBanner !== null"
        x-cloak
        class="custom-banner"
        data-banner-id="announcement"
    >
        <div class="custom-banner__inner">
            <div class="custom-banner__content">
                <div class="custom-banner__content-position">
                    <template x-if="currentBanner?.image_url">
                        <img class="custom-banner__image" :src="currentBanner.image_url" alt="Announcement image" width="36" height="36">
                    </template>

                    <span class="custom-banner__content-text">
                        <template x-if="currentBanner?.eyebrow">
                            <strong x-text="currentBanner.eyebrow"></strong>
                        </template>
                        <span x-text="currentBanner?.message"></span>
                    </span>
                </div>

                <template x-if="currentBanner?.link_url">
                    <a :href="currentBanner.link_url" target="_blank" class="custom-banner__link" rel="noreferrer noopener">
                        <span x-text="currentBanner.link_label || 'Learn more'"></span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </template>
            </div>

            <button type="button" class="custom-banner__close" @click="dismissCurrent()" aria-label="Dismiss announcement">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    @once
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('announcementBanner', (banners) => ({
                    banners: banners ?? [],
                    visibleBanners: [],
                    dismissedBannerIds: [],
                    currentBanner: null,
                    currentIndex: 0,
                    rotationIntervalId: null,
                    rotationMs: 6000,
                    storageKey: 'announcement-banner-dismissed',
                    init() {
                        this.dismissedBannerIds = this.loadDismissed();
                        this.refreshVisibleBanners();
                    },
                    dismissCurrent() {
                        if (!this.currentBanner) {
                            return;
                        }

                        if (!this.dismissedBannerIds.includes(this.currentBanner.id)) {
                            this.dismissedBannerIds.push(this.currentBanner.id);
                        }

                        this.storeDismissed();
                        this.refreshVisibleBanners();
                    },
                    nextBanner() {
                        if (this.visibleBanners.length === 0) {
                            this.currentBanner = null;
                            this.currentIndex = 0;
                            return;
                        }

                        this.currentIndex = (this.currentIndex + 1) % this.visibleBanners.length;
                        this.currentBanner = this.visibleBanners[this.currentIndex] ?? null;
                    },
                    refreshVisibleBanners() {
                        const activeBannerId = this.currentBanner?.id ?? null;

                        this.visibleBanners = this.banners.filter((banner) => !this.dismissedBannerIds.includes(banner.id));

                        if (this.visibleBanners.length === 0) {
                            this.currentBanner = null;
                            this.currentIndex = 0;
                            this.stopRotation();
                            return;
                        }

                        if (activeBannerId) {
                            const activeIndex = this.visibleBanners.findIndex((banner) => banner.id === activeBannerId);
                            this.currentIndex = activeIndex >= 0 ? activeIndex : Math.min(this.currentIndex, this.visibleBanners.length - 1);
                        } else {
                            this.currentIndex = 0;
                        }

                        this.currentBanner = this.visibleBanners[this.currentIndex] ?? this.visibleBanners[0];
                        this.startRotation();
                    },
                    startRotation() {
                        this.stopRotation();

                        if (this.visibleBanners.length <= 1) {
                            return;
                        }

                        this.rotationIntervalId = setInterval(() => {
                            this.nextBanner();
                        }, this.rotationMs);
                    },
                    stopRotation() {
                        if (!this.rotationIntervalId) {
                            return;
                        }

                        clearInterval(this.rotationIntervalId);
                        this.rotationIntervalId = null;
                    },
                    loadDismissed() {
                        try {
                            const raw = localStorage.getItem(this.storageKey);
                            const parsed = JSON.parse(raw ?? '[]');

                            return Array.isArray(parsed) ? parsed : [];
                        } catch (error) {
                            return [];
                        }
                    },
                    storeDismissed() {
                        try {
                            localStorage.setItem(this.storageKey, JSON.stringify(this.dismissedBannerIds));
                        } catch (error) {
                        }
                    },
                }));
            });
        </script>
    @endonce
@endif
