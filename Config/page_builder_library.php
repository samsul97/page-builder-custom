<?php

return [
    'items' => [
        [
            'key' => 'theme-rawdee-main-site',
            'name' => 'Rawdee Main Site Theme',
            'type' => 'theme',
            'category' => 'theme',
            'status' => 'enabled',
            'source' => 'internal',
            'description' => 'Internal baseline theme family derived from the current public Rawdee website.',
            'related_preset_key' => 'rawdee-main-site',
        ],
        [
            'key' => 'block-pack-landing-basics',
            'name' => 'Landing Basics Block Pack',
            'type' => 'plugin',
            'category' => 'block',
            'status' => 'planned',
            'source' => 'internal',
            'description' => 'Curated starter pack for hero, story, feature grid, and CTA patterns used by builder landing pages.',
            'related_preset_key' => 'rawdee-main-site',
            'activation' => [
                'contract' => 'block_pack',
                'block_types' => ['hero', 'text', 'feature_grid', 'cta'],
                'reusable_blocks' => [
                    [
                        'key' => 'rawdee-landing-hero-story',
                        'name' => 'Rawdee Landing Hero + Story',
                        'slug' => 'rawdee-landing-hero-story-pack',
                        'description' => 'Starter reusable section pack for a Rawdee-style landing page intro.',
                        'is_active' => true,
                        'blocks' => [
                            [
                                'type' => 'hero',
                                'data' => [
                                    'eyebrow' => 'Rawdee Experience',
                                    'title' => 'Glamping landing page built for direct bookings',
                                    'subtitle' => 'Use this starter hero as a safe baseline before customizing campaign copy.',
                                    'button_label' => 'Book Now',
                                    'button_url' => '#booking',
                                    'align' => 'left',
                                ],
                            ],
                            [
                                'type' => 'text',
                                'data' => [
                                    'title' => 'Tell the campaign story',
                                    'content' => 'Replace this section with offer details, seasonal context, or a short destination narrative.',
                                    'align' => 'left',
                                ],
                            ],
                        ],
                    ],
                    [
                        'key' => 'rawdee-feature-cta',
                        'name' => 'Rawdee Feature Grid + CTA',
                        'slug' => 'rawdee-feature-cta-pack',
                        'description' => 'Reusable feature highlights followed by a booking CTA.',
                        'is_active' => true,
                        'blocks' => [
                            [
                                'type' => 'feature_grid',
                                'data' => [
                                    'title' => 'Why guests choose Rawdee',
                                    'subtitle' => 'Edit these feature cards to match the current campaign.',
                                    'items' => [
                                        [
                                            'title' => 'Nature-first stay',
                                            'description' => 'Highlight the strongest property experience.',
                                        ],
                                        [
                                            'title' => 'Easy booking',
                                            'description' => 'Point visitors to the next action.',
                                        ],
                                        [
                                            'title' => 'Campaign-ready',
                                            'description' => 'Use separated builder ads tracking for landing pages.',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'type' => 'cta',
                                'data' => [
                                    'title' => 'Ready to book your stay?',
                                    'content' => 'Customize this CTA per landing page campaign.',
                                    'button_label' => 'Contact Rawdee',
                                    'button_url' => '#contact',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        [
            'key' => 'system-builder-ads-scope',
            'name' => 'Builder Ads Scope',
            'type' => 'plugin',
            'category' => 'system',
            'status' => 'enabled',
            'source' => 'internal',
            'description' => 'System-level separation between ads-general and ads-builder for page builder public pages.',
            'related_preset_key' => null,
        ],
    ],
];
