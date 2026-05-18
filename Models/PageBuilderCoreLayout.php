<?php

namespace Modules\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageBuilderCoreLayout extends Model
{
    protected $table = 'pb_core_layouts';

    protected $fillable = [
        'key',
        'name',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public static function defaultRecord(?array $seedSettings = null): self
    {
        $layout = static::query()->firstOrCreate(
            ['key' => 'default'],
            [
                'name' => 'Default Core Layout',
                'is_active' => true,
                'settings' => static::mergeSettings($seedSettings),
            ]
        );

        $layout->settings = static::mergeSettings($layout->settings ?: $seedSettings);

        return $layout;
    }

    public static function defaultSettings(): array
    {
        return [
            'font_family' => '"Plus Jakarta Sans", sans-serif',
            'heading_font_family' => '"Fraunces", serif',
            'background_color' => '#f7f3ea',
            'card_color' => '#ffffff',
            'accent_color' => '#c46f35',
            'text_color' => '#17261d',
            'muted_text_color' => '#5c6c63',
            'button_radius' => '999px',
            'container_width' => '1200px',
            'section_spacing' => '5rem',
        ];
    }

    public static function mergeSettings(?array $settings = null): array
    {
        return array_merge(static::defaultSettings(), $settings ?? []);
    }

    public function chromeLayouts(): HasMany
    {
        return $this->hasMany(PageBuilderLayout::class, 'core_layout_id');
    }
}
