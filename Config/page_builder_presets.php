<?php

return [
    'presets' => [
        [
            'key' => 'rawdee-main-site',
            'name' => 'Rawdee Main Site Baseline',
            'status' => 'internal-baseline',
            'category' => 'theme',
            'description' => 'First internal preset family that mirrors the current public Rawdee website as closely as practical.',
            'origin' => [
                'type' => 'internal',
                'source' => 'current-front-site',
                'note' => 'Controlled translation from the existing website into builder-compatible records. Not a blind Blade import.',
            ],
            'family' => [
                'theme_key' => 'glampings',
                'theme_name' => 'Glampings',
                'supports_start_from_template' => true,
                'supports_custom_extension' => true,
                'recommended_library_asset_keys' => [
                    'theme-rawdee-main-site',
                    'block-pack-landing-basics',
                    'system-builder-ads-scope',
                ],
            ],
            'blueprint' => [
                'core_layout' => [
                    'key' => 'preset-rawdee-main-site',
                    'name' => 'Preset: Rawdee Main Site Core',
                    'settings' => [
                        'font_family' => '"Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                        'heading_font_family' => '"Fraunces", serif',
                        'background_color' => '#f7f3ea',
                        'card_color' => '#ffffff',
                        'accent_color' => '#c46f35',
                        'text_color' => '#17261d',
                        'muted_text_color' => '#5c6c63',
                        'button_radius' => '999px',
                        'container_width' => '1200px',
                        'section_spacing' => '5rem',
                    ],
                ],
                'chrome_layout' => [
                    'key' => 'preset-rawdee-main-site',
                    'name' => 'Preset: Rawdee Main Site Chrome',
                    'inherits_core_layout_key' => 'preset-rawdee-main-site',
                    'settings' => [
                        'header' => [
                            'variant' => 'classic',
                            'brand_name' => 'RAWDEE',
                            'tagline' => null,
                            'button_label' => 'WhatsApp Us',
                            'button_url' => 'https://wa.me/1234567890',
                            'links' => [
                                ['type' => 'link', 'label' => 'Home', 'url' => '/'],
                                ['type' => 'megamenu', 'label' => 'Divisions', 'url' => '/pillars'],
                                ['type' => 'link', 'label' => 'Insights', 'url' => '/insights'],
                                ['type' => 'link', 'label' => 'About', 'url' => '/about'],
                                ['type' => 'link', 'label' => 'Contact', 'url' => '/contact'],
                            ],
                        ],
                        'navigation' => [
                            'style' => 'inline',
                            'is_sticky' => true,
                            'show_top_bar' => false,
                        ],
                        'footer' => [
                            'variant' => 'columns',
                            'source' => 'existing-site-settings-footer',
                            'note' => 'Footer content remains aligned with the current website settings so the first preset family matches the live site.',
                        ],
                    ],
                ],
                'starter_pages' => [
                    [
                        'key' => 'landing-hero-story',
                        'name' => 'Landing: Hero + Story + CTA',
                        'purpose' => 'General marketing landing page based on the current Rawdee visual system.',
                        'page_settings' => [
                            'layout_mode' => 'include',
                            'show_header' => true,
                            'show_footer' => true,
                            'content_mode' => 'builder',
                        ],
                        'block_recipe' => [
                            'hero',
                            'story',
                            'feature-grid',
                            'cta',
                        ],
                    ],
                    [
                        'key' => 'landing-raw-html',
                        'name' => 'Landing: Raw HTML With Builder Theme',
                        'purpose' => 'Lets teams keep custom markup while still inheriting the builder layout family and builder ads scope.',
                        'page_settings' => [
                            'layout_mode' => 'exclude',
                            'show_header' => false,
                            'show_footer' => false,
                            'content_mode' => 'raw_html',
                        ],
                        'block_recipe' => [],
                    ],
                ],
                'future_controls' => [
                    'content',
                    'page-settings',
                    'seo',
                    'builder-ads-info',
                    'limited-theme-overrides',
                ],
            ],
            'notes' => [
                'This preset family should become the first Start From Template experience.',
                'The initial implementation should reuse the current builder engine instead of replacing it.',
                'Future plugin/theme import should extend this catalog, not bypass it.',
            ],
        ],
    ],
];
