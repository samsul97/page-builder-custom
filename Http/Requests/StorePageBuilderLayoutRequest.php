<?php

namespace Modules\PageBuilder\Http\Requests;

use App\Models\PageBuilder\PageBuilderLayout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePageBuilderLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mapSimpleLinks = fn ($items) => collect($items)
            ->map(fn ($item) => [
                'label' => trim((string) data_get($item, 'label')),
                'url' => trim((string) data_get($item, 'url')),
            ])
            ->filter(fn ($item) => filled($item['label']) || filled($item['url']))
            ->values()
            ->all();

        $parseJsonArray = function ($value): array {
            if (is_array($value)) {
                return $value;
            }

            $value = trim((string) $value);

            if ($value === '') {
                return [];
            }

            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        };

        $mapNavigationLinks = function ($items) use ($parseJsonArray): array {
            return collect($items)
                ->map(function ($item) use ($parseJsonArray) {
                    $children = collect($parseJsonArray(data_get($item, 'children_json')))
                        ->map(fn ($child) => [
                            'label' => trim((string) data_get($child, 'label')),
                            'url' => trim((string) data_get($child, 'url')),
                        ])
                        ->filter(fn ($child) => filled($child['label']) || filled($child['url']))
                        ->values()
                        ->all();

                    $sections = collect($parseJsonArray(data_get($item, 'sections_json')))
                        ->map(function ($section) {
                            return [
                                'title' => trim((string) data_get($section, 'title')),
                                'links' => collect(data_get($section, 'links', []))
                                    ->map(fn ($link) => [
                                        'label' => trim((string) data_get($link, 'label')),
                                        'url' => trim((string) data_get($link, 'url')),
                                    ])
                                    ->filter(fn ($link) => filled($link['label']) || filled($link['url']))
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->filter(fn ($section) => filled($section['title']) || count($section['links']) > 0)
                        ->values()
                        ->all();

                    return [
                        'type' => trim((string) data_get($item, 'type', 'link')),
                        'label' => trim((string) data_get($item, 'label')),
                        'url' => trim((string) data_get($item, 'url')),
                        'children' => $children,
                        'sections' => $sections,
                    ];
                })
                ->filter(fn ($item) => filled($item['label']) || filled($item['url']) || count($item['children']) > 0 || count($item['sections']) > 0)
                ->values()
                ->all();
        };

        $contacts = collect($this->input('footer_contacts', []))
            ->map(fn ($item) => [
                'label' => trim((string) data_get($item, 'label')),
                'phone' => trim((string) data_get($item, 'phone')),
            ])
            ->filter(fn ($item) => filled($item['label']) || filled($item['phone']))
            ->values()
            ->all();

        $locations = collect($this->input('footer_locations', []))
            ->map(function ($item) {
                return [
                    'label' => trim((string) data_get($item, 'label')),
                    'map_url' => trim((string) data_get($item, 'map_url')),
                    'lines' => collect(data_get($item, 'lines', []))->map(fn ($line) => trim((string) $line))->filter()->values()->all(),
                    'weekday_label' => trim((string) data_get($item, 'weekday_label')),
                    'weekday_value' => trim((string) data_get($item, 'weekday_value')),
                    'weekend_label' => trim((string) data_get($item, 'weekend_label')),
                    'weekend_value' => trim((string) data_get($item, 'weekend_value')),
                ];
            })
            ->filter(fn ($item) => filled($item['label']) || filled($item['map_url']) || count($item['lines']) > 0)
            ->values()
            ->all();

        $name = trim((string) $this->input('name', ''));
        $keyInput = trim((string) $this->input('key', ''));

        $this->merge([
            'name' => $name,
            'key' => Str::slug($keyInput !== '' ? $keyInput : $name),
            'core_layout_id' => $this->filled('core_layout_id') ? (int) $this->input('core_layout_id') : null,
            'is_active' => $this->boolean('is_active'),
            'header_variant' => trim((string) $this->input('header_variant', PageBuilderLayout::HEADER_VARIANT_CLASSIC)),
            'header_brand_name' => trim((string) $this->input('header_brand_name')),
            'header_brand_logo_url' => trim((string) $this->input('header_brand_logo_url')),
            'header_brand_logo_alt' => trim((string) $this->input('header_brand_logo_alt')),
            'header_tagline' => trim((string) $this->input('header_tagline')),
            'header_button_label' => trim((string) $this->input('header_button_label')),
            'header_button_url' => trim((string) $this->input('header_button_url')),
            'navigation_style' => trim((string) $this->input('navigation_style', 'inline')),
            'navigation_density' => trim((string) $this->input('navigation_density', 'comfortable')),
            'navigation_is_sticky' => $this->boolean('navigation_is_sticky'),
            'navigation_show_top_bar' => $this->boolean('navigation_show_top_bar'),
            'navigation_top_bar_text' => trim((string) $this->input('navigation_top_bar_text')),
            'navigation_top_bar_link_label' => trim((string) $this->input('navigation_top_bar_link_label')),
            'navigation_top_bar_link_url' => trim((string) $this->input('navigation_top_bar_link_url')),
            'navigation_meta_label' => trim((string) $this->input('navigation_meta_label')),
            'navigation_meta_value' => trim((string) $this->input('navigation_meta_value')),
            'navigation_meta_url' => trim((string) $this->input('navigation_meta_url')),
            'chrome_header_surface_style' => trim((string) $this->input('chrome_header_surface_style', 'glass')),
            'footer_variant' => trim((string) $this->input('footer_variant', PageBuilderLayout::FOOTER_VARIANT_COLUMNS)),
            'footer_surface_style' => trim((string) $this->input('footer_surface_style', 'dark')),
            'footer_brand_title' => trim((string) $this->input('footer_brand_title')),
            'footer_brand_text' => trim((string) $this->input('footer_brand_text')),
            'footer_social_title' => trim((string) $this->input('footer_social_title')),
            'footer_journey_title' => trim((string) $this->input('footer_journey_title')),
            'footer_contact_title' => trim((string) $this->input('footer_contact_title')),
            'footer_location_title' => trim((string) $this->input('footer_location_title')),
            'footer_hours_title' => trim((string) $this->input('footer_hours_title')),
            'footer_bottom_origin' => trim((string) $this->input('footer_bottom_origin')),
            'footer_copyright' => trim((string) $this->input('footer_copyright')),
            'header_links' => $mapNavigationLinks($this->input('header_links', [])),
            'footer_social_links' => $mapSimpleLinks($this->input('footer_social_links', [])),
            'footer_journey_links' => $mapSimpleLinks($this->input('footer_journey_links', [])),
            'footer_contacts' => $contacts,
            'footer_locations' => $locations,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'key' => ['required', 'string', 'max:190', 'unique:pb_layouts,key'],
            'core_layout_id' => ['required', 'integer', Rule::exists('pb_core_layouts', 'id')],
            'is_active' => ['sometimes', 'boolean'],
            'header_variant' => ['required', 'string', 'in:' . implode(',', [
                PageBuilderLayout::HEADER_VARIANT_CLASSIC,
                PageBuilderLayout::HEADER_VARIANT_CENTERED,
            ])],
            'header_brand_name' => ['required', 'string', 'max:190'],
            'header_brand_logo_url' => ['nullable', 'string', 'max:1000'],
            'header_brand_logo_alt' => ['nullable', 'string', 'max:190'],
            'header_tagline' => ['nullable', 'string', 'max:255'],
            'header_button_label' => ['nullable', 'string', 'max:120'],
            'header_button_url' => ['nullable', 'string', 'max:500'],
            'header_links' => ['array'],
            'header_links.*.type' => ['required', 'string', 'in:link,button,dropdown,megamenu'],
            'header_links.*.label' => ['nullable', 'string', 'max:120'],
            'header_links.*.url' => ['nullable', 'string', 'max:500'],
            'header_links.*.children' => ['array'],
            'header_links.*.children.*.label' => ['nullable', 'string', 'max:120'],
            'header_links.*.children.*.url' => ['nullable', 'string', 'max:500'],
            'header_links.*.sections' => ['array'],
            'header_links.*.sections.*.title' => ['nullable', 'string', 'max:120'],
            'header_links.*.sections.*.links' => ['array'],
            'header_links.*.sections.*.links.*.label' => ['nullable', 'string', 'max:120'],
            'header_links.*.sections.*.links.*.url' => ['nullable', 'string', 'max:500'],
            'navigation_style' => ['required', 'string', 'in:inline,pill'],
            'navigation_density' => ['required', 'string', 'in:compact,comfortable,relaxed'],
            'navigation_is_sticky' => ['sometimes', 'boolean'],
            'navigation_show_top_bar' => ['sometimes', 'boolean'],
            'navigation_top_bar_text' => ['nullable', 'string', 'max:255'],
            'navigation_top_bar_link_label' => ['nullable', 'string', 'max:120'],
            'navigation_top_bar_link_url' => ['nullable', 'string', 'max:500'],
            'navigation_meta_label' => ['nullable', 'string', 'max:120'],
            'navigation_meta_value' => ['nullable', 'string', 'max:190'],
            'navigation_meta_url' => ['nullable', 'string', 'max:500'],
            'chrome_header_surface_style' => ['required', 'string', 'in:solid,glass,minimal'],
            'footer_brand_title' => ['nullable', 'string', 'max:190'],
            'footer_variant' => ['required', 'string', 'in:' . implode(',', [
                PageBuilderLayout::FOOTER_VARIANT_COLUMNS,
                PageBuilderLayout::FOOTER_VARIANT_MINIMAL,
            ])],
            'footer_surface_style' => ['required', 'string', 'in:dark,soft,light'],
            'footer_brand_text' => ['nullable', 'string'],
            'footer_social_title' => ['nullable', 'string', 'max:190'],
            'footer_social_links' => ['array'],
            'footer_social_links.*.label' => ['nullable', 'string', 'max:120'],
            'footer_social_links.*.url' => ['nullable', 'string', 'max:500'],
            'footer_journey_title' => ['nullable', 'string', 'max:190'],
            'footer_journey_links' => ['array'],
            'footer_journey_links.*.label' => ['nullable', 'string', 'max:120'],
            'footer_journey_links.*.url' => ['nullable', 'string', 'max:500'],
            'footer_contact_title' => ['nullable', 'string', 'max:190'],
            'footer_contacts' => ['array'],
            'footer_contacts.*.label' => ['nullable', 'string', 'max:120'],
            'footer_contacts.*.phone' => ['nullable', 'string', 'max:120'],
            'footer_location_title' => ['nullable', 'string', 'max:190'],
            'footer_hours_title' => ['nullable', 'string', 'max:190'],
            'footer_locations' => ['array'],
            'footer_locations.*.label' => ['nullable', 'string', 'max:190'],
            'footer_locations.*.map_url' => ['nullable', 'string', 'max:500'],
            'footer_locations.*.lines' => ['array'],
            'footer_locations.*.lines.*' => ['nullable', 'string', 'max:255'],
            'footer_locations.*.weekday_label' => ['nullable', 'string', 'max:120'],
            'footer_locations.*.weekday_value' => ['nullable', 'string', 'max:120'],
            'footer_locations.*.weekend_label' => ['nullable', 'string', 'max:120'],
            'footer_locations.*.weekend_value' => ['nullable', 'string', 'max:120'],
            'footer_bottom_origin' => ['nullable', 'string', 'max:255'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function layoutPayload(): array
    {
        return [
            'name' => $this->validated('name'),
            'key' => $this->validated('key'),
            'core_layout_id' => $this->validated('core_layout_id'),
            'is_active' => (bool) $this->validated('is_active', false),
            'settings' => [
                'header' => [
                    'variant' => $this->validated('header_variant'),
                    'brand_name' => $this->validated('header_brand_name'),
                    'brand_logo_url' => $this->validated('header_brand_logo_url'),
                    'brand_logo_alt' => $this->validated('header_brand_logo_alt'),
                    'tagline' => $this->validated('header_tagline'),
                    'button_label' => $this->validated('header_button_label'),
                    'button_url' => $this->validated('header_button_url'),
                    'links' => $this->validated('header_links', []),
                ],
                'navigation' => [
                    'style' => $this->validated('navigation_style'),
                    'density' => $this->validated('navigation_density'),
                    'is_sticky' => (bool) $this->validated('navigation_is_sticky', false),
                    'show_top_bar' => (bool) $this->validated('navigation_show_top_bar', false),
                    'top_bar_text' => $this->validated('navigation_top_bar_text'),
                    'top_bar_link_label' => $this->validated('navigation_top_bar_link_label'),
                    'top_bar_link_url' => $this->validated('navigation_top_bar_link_url'),
                    'meta_label' => $this->validated('navigation_meta_label'),
                    'meta_value' => $this->validated('navigation_meta_value'),
                    'meta_url' => $this->validated('navigation_meta_url'),
                ],
                'chrome_visual' => [
                    'header_surface_style' => $this->validated('chrome_header_surface_style'),
                ],
                'footer' => [
                    'variant' => $this->validated('footer_variant'),
                    'surface_style' => $this->validated('footer_surface_style'),
                    'brand_title' => $this->validated('footer_brand_title'),
                    'brand_text' => $this->validated('footer_brand_text'),
                    'social_title' => $this->validated('footer_social_title'),
                    'social_links' => $this->validated('footer_social_links', []),
                    'journey_title' => $this->validated('footer_journey_title'),
                    'journey_links' => $this->validated('footer_journey_links', []),
                    'contact_title' => $this->validated('footer_contact_title'),
                    'contacts' => $this->validated('footer_contacts', []),
                    'location_title' => $this->validated('footer_location_title'),
                    'hours_title' => $this->validated('footer_hours_title'),
                    'locations' => $this->validated('footer_locations', []),
                    'bottom_origin' => $this->validated('footer_bottom_origin'),
                    'copyright' => $this->validated('footer_copyright'),
                ],
            ],
        ];
    }
}
