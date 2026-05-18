<?php

namespace Modules\PageBuilder\Http\Requests;

use App\Models\PageBuilder\PageBuilderCoreLayout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePageBuilderCoreLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $keyInput = trim((string) $this->input('key', ''));

        $this->merge([
            'name' => $name,
            'key' => Str::slug($keyInput !== '' ? $keyInput : $name),
            'is_active' => $this->boolean('is_active'),
            'font_family' => trim((string) $this->input('font_family')),
            'heading_font_family' => trim((string) $this->input('heading_font_family')),
            'background_color' => trim((string) $this->input('background_color')),
            'card_color' => trim((string) $this->input('card_color')),
            'accent_color' => trim((string) $this->input('accent_color')),
            'text_color' => trim((string) $this->input('text_color')),
            'muted_text_color' => trim((string) $this->input('muted_text_color')),
            'button_radius' => trim((string) $this->input('button_radius')),
            'container_width' => trim((string) $this->input('container_width')),
            'section_spacing' => trim((string) $this->input('section_spacing')),
        ]);
    }

    public function rules(): array
    {
        /** @var PageBuilderCoreLayout|null $coreLayout */
        $coreLayout = $this->route('pageBuilderCoreLayout');

        return [
            'name' => ['required', 'string', 'max:190'],
            'key' => ['required', 'string', 'max:190', Rule::unique('pb_core_layouts', 'key')->ignore($coreLayout?->id)],
            'is_active' => ['sometimes', 'boolean'],
            'font_family' => ['required', 'string', 'max:190'],
            'heading_font_family' => ['required', 'string', 'max:190'],
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

    public function coreLayoutPayload(): array
    {
        return [
            'name' => $this->validated('name'),
            'key' => $this->validated('key'),
            'is_active' => (bool) $this->validated('is_active', false),
            'settings' => array_merge(PageBuilderCoreLayout::defaultSettings(), [
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
            ]),
        ];
    }
}
