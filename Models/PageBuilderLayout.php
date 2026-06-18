<?php

namespace Modules\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBuilderLayout extends Model
{
    protected $table = 'pb_layouts';

    public const HEADER_VARIANT_CLASSIC = 'classic';

    public const HEADER_VARIANT_CENTERED = 'centered';

    public const FOOTER_VARIANT_COLUMNS = 'columns';

    public const FOOTER_VARIANT_MINIMAL = 'minimal';

    protected $fillable = [
        'key',
        'name',
        'core_layout_id',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public static function defaultRecord(): self
    {
        $layout = static::query()->firstOrCreate(
            ['key' => 'default'],
            [
                'name' => 'Default Page Builder Layout',
                'core_layout_id' => PageBuilderCoreLayout::defaultRecord()->id,
                'is_active' => true,
                'settings' => static::defaultSettings(),
            ]
        );

        if (! $layout->core_layout_id) {
            $seedSettings = static::legacyThemeSettings($layout->settings);
            $layout->forceFill([
                'core_layout_id' => PageBuilderCoreLayout::defaultRecord($seedSettings)->id,
            ])->save();
        }

        $layout->settings = static::mergeSettings($layout->settings);

        return $layout;
    }

    public static function defaultSettings(): array
    {
        return [
            'header' => [
                'variant' => static::HEADER_VARIANT_CLASSIC,
                'brand_name' => config('app.name', 'RAWDEE'),
                'brand_logo_url' => null,
                'brand_logo_alt' => config('app.name', 'RAWDEE') . ' logo',
                'tagline' => null,
                'button_label' => 'WhatsApp Us',
                'button_url' => 'https://wa.me/1234567890',
                'links' => [
                    ['type' => 'link', 'label' => 'Home', 'url' => '/', 'children' => [], 'sections' => []],
                    ['type' => 'megamenu', 'label' => 'Divisions', 'url' => '/pillars', 'children' => [], 'sections' => [
                        [
                            'title' => 'Perjalanan Rawdee',
                            'links' => [
                                ['label' => 'Rawdee Coffee House', 'url' => '/pillars/coffeehouse'],
                                ['label' => 'Rawdee Coffee Plantation', 'url' => '/pillars/plantation'],
                                ['label' => 'Rawdee Roastery', 'url' => '/pillars/roastery'],
                                ['label' => 'Rawdee Glamping', 'url' => '/pillars/tripglamping'],
                            ],
                        ],
                        [
                            'title' => 'Explore',
                            'links' => [
                                ['label' => 'About Rawdee', 'url' => '/about'],
                                ['label' => 'Insights', 'url' => '/insights'],
                                ['label' => 'Contact', 'url' => '/contact'],
                            ],
                        ],
                    ]],
                    ['type' => 'link', 'label' => 'Insights', 'url' => '/insights', 'children' => [], 'sections' => []],
                    ['type' => 'link', 'label' => 'About', 'url' => '/about', 'children' => [], 'sections' => []],
                    ['type' => 'link', 'label' => 'Contact', 'url' => '/contact', 'children' => [], 'sections' => []],
                ],
            ],
            'navigation' => [
                'style' => 'inline',
                'density' => 'comfortable',
                'is_sticky' => true,
                'show_top_bar' => false,
                'top_bar_text' => null,
                'top_bar_link_label' => null,
                'top_bar_link_url' => null,
                'meta_label' => null,
                'meta_value' => null,
                'meta_url' => null,
            ],
            'footer' => [
                'variant' => static::FOOTER_VARIANT_COLUMNS,
                'surface_style' => 'dark',
                'brand_title' => site_setting('footer_brand_title', 'RAWDEE'),
                'brand_text' => site_setting('footer_brand_text', 'Kopi yang bisa ditelusuri — dari kebun di Rawageude sampai ke tangan yang menikmatinya.'),
                'social_title' => site_setting('footer_social_title', 'Social Media'),
                'social_links' => [
                    ['label' => site_setting('footer_social_instagram_label', 'Instagram'), 'url' => site_setting('footer_social_instagram_url', 'https://instagram.com/')],
                    ['label' => site_setting('footer_social_tiktok_label', 'TikTok'), 'url' => site_setting('footer_social_tiktok_url', 'https://www.tiktok.com/')],
                    ['label' => site_setting('footer_social_youtube_label', 'Youtube'), 'url' => site_setting('footer_social_youtube_url', 'https://www.youtube.com/')],
                ],
                'journey_title' => site_setting('footer_journey_title', 'Perjalanan Rawdee'),
                'journey_links' => [
                    ['label' => site_setting('footer_journey_coffeehouse_label', 'Rawdee Coffee House'), 'url' => site_setting('footer_journey_coffeehouse_url', '/pillars/coffeehouse')],
                    ['label' => site_setting('footer_journey_plantation_label', 'Rawdee Coffee Plantation'), 'url' => site_setting('footer_journey_plantation_url', '/pillars/plantation')],
                    ['label' => site_setting('footer_journey_roastery_label', 'Rawdee Roastery'), 'url' => site_setting('footer_journey_roastery_url', '/pillars/roastery')],
                    ['label' => site_setting('footer_journey_glamping_label', 'Rawdee Glamping'), 'url' => site_setting('footer_journey_glamping_url', '/pillars/tripglamping')],
                ],
                'contact_title' => site_setting('footer_contact_title', 'Kontak'),
                'contacts' => [
                    ['label' => site_setting('footer_contact_head_office_label', 'Head Office'), 'phone' => site_setting('footer_contact_head_office_phone', '+62 852-1312-4003')],
                    ['label' => site_setting('footer_contact_coffeehouse_label', 'Rawdee Coffee House'), 'phone' => site_setting('footer_contact_coffeehouse_phone', '+62 857-1816-5658')],
                    ['label' => site_setting('footer_contact_roastery_label', 'Rawdee Roastery'), 'phone' => site_setting('footer_contact_roastery_phone', '+62 812-2781-9471')],
                    ['label' => site_setting('footer_contact_glamping_label', 'Rawdee Glamping'), 'phone' => site_setting('footer_contact_glamping_phone', '+62 851-7245-8186')],
                ],
                'location_title' => site_setting('footer_location_title', 'Lokasi'),
                'hours_title' => site_setting('footer_location_hours_title', 'Jam Operasional'),
                'locations' => [
                    [
                        'label' => site_setting('footer_location_hq_label', 'Rawdee Coffee House & Roastery'),
                        'map_url' => site_setting('footer_location_hq_map_url', 'https://maps.app.goo.gl/VB2GzCTLFjV6asGy9'),
                        'lines' => [
                            site_setting('footer_location_hq_line_1', 'Blok HB2, Jl. Kasuari No.3'),
                            site_setting('footer_location_hq_line_2', 'Pondok Pucung, Pondok Aren'),
                            site_setting('footer_location_hq_line_3', 'Tangerang Selatan'),
                        ],
                        'weekday_label' => site_setting('footer_location_weekday_label', 'Weekday'),
                        'weekday_value' => site_setting('footer_location_weekday_value', '10.00 - 23.00'),
                        'weekend_label' => site_setting('footer_location_weekend_label', 'Weekend'),
                        'weekend_value' => site_setting('footer_location_weekend_value', '08.00 - 23.00'),
                    ],
                    [
                        'label' => site_setting('footer_location_plantation_label', 'Rawdee Coffee Plantation & Glamping'),
                        'map_url' => site_setting('footer_location_plantation_map_url', 'https://maps.app.goo.gl/BrUWfAPtLKTETPpT8'),
                        'lines' => [
                            site_setting('footer_location_plantation_line_1', 'Jl. Rawa Gede'),
                            site_setting('footer_location_plantation_line_2', 'Sirnajaya, Sukamakmur'),
                            site_setting('footer_location_plantation_line_3', 'Bogor'),
                        ],
                    ],
                ],
                'bottom_origin' => site_setting('footer_bottom_origin', 'From Rawageude, Bogor - Indonesia'),
                'copyright' => '© ' . now()->year . ' Rawdee',
            ],
            'chrome_visual' => [
                'header_surface_style' => 'glass',
            ],
        ];
    }

    public static function mergeSettings(?array $settings = null): array
    {
        return array_replace_recursive(static::defaultSettings(), $settings ?? []);
    }

    public static function legacyThemeSettings(?array $settings = null): array
    {
        return array_filter([
            'background_color' => data_get($settings, 'theme.background_color'),
            'card_color' => data_get($settings, 'theme.card_color'),
            'accent_color' => data_get($settings, 'theme.accent_color'),
            'text_color' => data_get($settings, 'theme.text_color'),
        ]);
    }

    public function coreLayout(): BelongsTo
    {
        return $this->belongsTo(PageBuilderCoreLayout::class, 'core_layout_id');
    }
}
