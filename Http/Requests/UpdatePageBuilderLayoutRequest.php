<?php

namespace Modules\PageBuilder\Http\Requests;

use App\Models\PageBuilder\PageBuilderCoreLayout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageBuilderLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mapLinks = fn ($items) => collect($items)
            ->map(fn ($item) => [
                'label' => trim((string) data_get($item, 'label')),
                'url' => trim((string) data_get($item, 'url')),
            ])
            ->filter(fn ($item) => filled($item['label']) || filled($item['url']))
            ->values()
            ->all();

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

        $this->merge([
            'header_brand_name' => trim((string) $this->input('header_brand_name')),
            'header_brand_logo_url' => trim((string) $this->input('header_brand_logo_url')),
            'header_brand_logo_alt' => trim((string) $this->input('header_brand_logo_alt')),
            'header_tagline' => trim((string) $this->input('header_tagline')),
            'header_button_label' => trim((string) $this->input('header_button_label')),
            'header_button_url' => trim((string) $this->input('header_button_url')),
            'core_layout_id' => $this->filled('core_layout_id') ? (int) $this->input('core_layout_id') : null,
            'core_layout_name' => trim((string) $this->input('core_layout_name')),
            'font_family' => trim((string) $this->input('font_family')),
            'heading_font_family' => trim((string) $this->input('heading_font_family')),
            'footer_brand_title' => trim((string) $this->input('footer_brand_title')),
            'footer_brand_text' => trim((string) $this->input('footer_brand_text')),
            'footer_social_title' => trim((string) $this->input('footer_social_title')),
            'footer_journey_title' => trim((string) $this->input('footer_journey_title')),
            'footer_contact_title' => trim((string) $this->input('footer_contact_title')),
            'footer_location_title' => trim((string) $this->input('footer_location_title')),
            'footer_hours_title' => trim((string) $this->input('footer_hours_title')),
            'footer_bottom_origin' => trim((string) $this->input('footer_bottom_origin')),
            'footer_copyright' => trim((string) $this->input('footer_copyright')),
            'background_color' => trim((string) $this->input('background_color')),
            'card_color' => trim((string) $this->input('card_color')),
            'accent_color' => trim((string) $this->input('accent_color')),
            'text_color' => trim((string) $this->input('text_color')),
            'muted_text_color' => trim((string) $this->input('muted_text_color')),
            'button_radius' => trim((string) $this->input('button_radius')),
            'container_width' => trim((string) $this->input('container_width')),
            'section_spacing' => trim((string) $this->input('section_spacing')),
            'header_links' => $mapLinks($this->input('header_links', [])),
            'footer_social_links' => $mapLinks($this->input('footer_social_links', [])),
            'footer_journey_links' => $mapLinks($this->input('footer_journey_links', [])),
            'footer_contacts' => $contacts,
            'footer_locations' => $locations,
        ]);
    }

    public function rules(): array
    {
        return [
            'core_layout_id' => ['required', 'integer', Rule::exists('pb_core_layouts', 'id')],
            'core_layout_name' => ['nullable', 'string', 'max:190'],
            'header_brand_name' => ['required', 'string', 'max:190'],
            'header_brand_logo_url' => ['nullable', 'string', 'max:1000'],
            'header_brand_logo_alt' => ['nullable', 'string', 'max:190'],
            'header_tagline' => ['nullable', 'string', 'max:255'],
            'header_button_label' => ['nullable', 'string', 'max:120'],
            'header_button_url' => ['nullable', 'string', 'max:500'],
            'font_family' => ['required', 'string', 'max:190'],
            'heading_font_family' => ['required', 'string', 'max:190'],
            'header_links' => ['array'],
            'header_links.*.label' => ['nullable', 'string', 'max:120'],
            'header_links.*.url' => ['nullable', 'string', 'max:500'],
            'footer_brand_title' => ['nullable', 'string', 'max:190'],
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
            'background_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'card_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'accent_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'text_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'muted_text_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'button_radius' => ['required', 'string', 'max:32'],
            'container_width' => ['required', 'string', 'max:32'],
            'section_spacing' => ['required', 'string', 'max:32'],
        ];
    }

    public function layoutPayload(): array
    {
        return [
            'core_layout_id' => $this->validated('core_layout_id'),
            'settings' => [
                'header' => [
                    'brand_name' => $this->validated('header_brand_name'),
                    'brand_logo_url' => $this->validated('header_brand_logo_url'),
                    'brand_logo_alt' => $this->validated('header_brand_logo_alt'),
                    'tagline' => $this->validated('header_tagline'),
                    'button_label' => $this->validated('header_button_label'),
                    'button_url' => $this->validated('header_button_url'),
                    'links' => $this->validated('header_links', []),
                ],
                'footer' => [
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

    public function coreLayoutPayload(): array
    {
        return [
            'name' => $this->validated('core_layout_name') ?: PageBuilderCoreLayout::query()->find($this->validated('core_layout_id'))?->name ?: 'Core Layout',
            'settings' => [
                'font_family' => $this->validated('font_family'),
                'heading_font_family' => $this->validated('heading_font_family'),
                'background_color' => $this->validated('background_color'),
                'card_color' => $this->validated('card_color'),
                'accent_color' => $this->validated('accent_color'),
                'text_color' => $this->validated('text_color'),
                'muted_text_color' => $this->validated('muted_text_color'),
                'button_radius' => $this->validated('button_radius'),
                'container_width' => $this->validated('container_width'),
                'section_spacing' => $this->validated('section_spacing'),
            ],
        ];
    }
}
