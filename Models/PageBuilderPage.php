<?php

namespace Modules\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilderPage extends Model
{
    protected $table = 'pb_pages';

    public const LAYOUT_MODE_INCLUDE = 'include';

    public const LAYOUT_MODE_EXCLUDE = 'exclude';

    public const CONTENT_MODE_BUILDER = 'builder';

    public const CONTENT_MODE_RAW_HTML = 'raw_html';

    protected $fillable = [
        'title',
        'slug',
        'blocks',
        'settings',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_path',
        'is_published',
    ];

    protected $casts = [
        'blocks' => 'array',
        'settings' => 'array',
        'is_published' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public static function defaultSettings(): array
    {
        return [
            'layout_mode' => static::LAYOUT_MODE_INCLUDE,
            'core_layout_id' => null,
            'chrome_layout_id' => null,
            'show_header' => true,
            'show_footer' => true,
            'content_mode' => static::CONTENT_MODE_BUILDER,
            'raw_markup' => null,
            'theme_overrides' => [
                'accent_color' => null,
                'button_radius' => null,
                'container_width' => null,
                'section_spacing' => null,
            ],
        ];
    }

    public function mergedSettings(): array
    {
        return array_merge(static::defaultSettings(), $this->settings ?? []);
    }

    public function ogImageUrl(): ?string
    {
        return $this->og_image_path ? uploads_url($this->og_image_path) : null;
    }
}
