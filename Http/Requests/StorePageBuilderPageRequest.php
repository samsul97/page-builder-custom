<?php

namespace Modules\PageBuilder\Http\Requests;

use App\Models\PageBuilder\PageBuilderLayout;
use App\Models\PageBuilder\PageBuilderPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePageBuilderPageRequest extends FormRequest
{
    protected ?array $decodedBlocks = null;

    protected bool $blocksJsonInvalid = false;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $title = trim((string) $this->input('title', ''));
        $slugInput = trim((string) $this->input('slug', ''));
        $blocksJson = trim((string) $this->input('blocks_json', ''));

        $this->merge([
            'title' => $title,
            'slug' => Str::slug($slugInput !== '' ? $slugInput : $title),
            'is_published' => $this->boolean('is_published'),
            'layout_mode' => (string) $this->input('layout_mode', PageBuilderPage::LAYOUT_MODE_INCLUDE),
            'show_header' => $this->boolean('show_header'),
            'show_footer' => $this->boolean('show_footer'),
            'content_mode' => (string) $this->input('content_mode', PageBuilderPage::CONTENT_MODE_BUILDER),
            'raw_markup' => (string) $this->input('raw_markup', ''),
            'core_layout_id' => $this->filled('core_layout_id') ? (int) $this->input('core_layout_id') : null,
            'chrome_layout_id' => $this->filled('chrome_layout_id') ? (int) $this->input('chrome_layout_id') : null,
            'theme_override_accent_color' => trim((string) $this->input('theme_override_accent_color', '')),
            'theme_override_button_radius' => trim((string) $this->input('theme_override_button_radius', '')),
            'theme_override_container_width' => trim((string) $this->input('theme_override_container_width', '')),
            'theme_override_section_spacing' => trim((string) $this->input('theme_override_section_spacing', '')),
        ]);

        if ($blocksJson === '') {
            $this->decodedBlocks = [];

            return;
        }

        $decoded = json_decode($blocksJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $this->blocksJsonInvalid = true;

            return;
        }

        $this->decodedBlocks = $decoded;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->blocksJsonInvalid) {
                $validator->errors()->add('blocks_json', 'Blocks JSON must be valid JSON array.');
            }

            $layoutMode = $this->input('layout_mode', PageBuilderPage::LAYOUT_MODE_INCLUDE);
            $coreLayoutId = $this->input('core_layout_id');
            $chromeLayoutId = $this->input('chrome_layout_id');

            if ($layoutMode === PageBuilderPage::LAYOUT_MODE_INCLUDE && filled($chromeLayoutId) && filled($coreLayoutId)) {
                $chromeLayout = PageBuilderLayout::query()->find($chromeLayoutId);

                if ($chromeLayout && (int) $chromeLayout->core_layout_id !== (int) $coreLayoutId) {
                    $validator->errors()->add('chrome_layout_id', 'Selected Chrome Layout does not belong to the selected Core Layout.');
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', 'unique:pb_pages,slug'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'og_image_path' => ['nullable', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
            'layout_mode' => ['required', 'string', 'in:' . implode(',', [
                PageBuilderPage::LAYOUT_MODE_INCLUDE,
                PageBuilderPage::LAYOUT_MODE_EXCLUDE,
            ])],
            'core_layout_id' => ['nullable', 'integer', Rule::exists('pb_core_layouts', 'id')],
            'chrome_layout_id' => ['nullable', 'integer', Rule::exists('pb_layouts', 'id')],
            'show_header' => ['sometimes', 'boolean'],
            'show_footer' => ['sometimes', 'boolean'],
            'content_mode' => ['required', 'string', 'in:' . implode(',', [
                PageBuilderPage::CONTENT_MODE_BUILDER,
                PageBuilderPage::CONTENT_MODE_RAW_HTML,
            ])],
            'raw_markup' => ['nullable', 'string'],
            'blocks_json' => ['nullable', 'string'],
            'theme_override_accent_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'theme_override_button_radius' => ['nullable', 'string', 'max:32'],
            'theme_override_container_width' => ['nullable', 'string', 'max:32'],
            'theme_override_section_spacing' => ['nullable', 'string', 'max:32'],
        ];
    }

    public function pagePayload(): array
    {
        return [
            'title' => $this->validated('title'),
            'slug' => $this->validated('slug'),
            'meta_title' => $this->validated('meta_title'),
            'meta_description' => $this->validated('meta_description'),
            'meta_keywords' => $this->validated('meta_keywords'),
            'og_image_path' => $this->validated('og_image_path'),
            'is_published' => (bool) $this->validated('is_published', false),
            'blocks' => $this->decodedBlocks ?? [],
            'settings' => [
                'layout_mode' => $this->validated('layout_mode'),
                'core_layout_id' => $this->validated('core_layout_id'),
                'chrome_layout_id' => $this->validated('layout_mode') === PageBuilderPage::LAYOUT_MODE_INCLUDE
                    ? $this->validated('chrome_layout_id')
                    : null,
                'show_header' => $this->validated('layout_mode') === PageBuilderPage::LAYOUT_MODE_INCLUDE
                    ? (bool) $this->validated('show_header', false)
                    : false,
                'show_footer' => $this->validated('layout_mode') === PageBuilderPage::LAYOUT_MODE_INCLUDE
                    ? (bool) $this->validated('show_footer', false)
                    : false,
                'content_mode' => $this->validated('content_mode'),
                'raw_markup' => trim((string) $this->validated('raw_markup', '')) ?: null,
                'theme_overrides' => [
                    'accent_color' => $this->validated('theme_override_accent_color') ?: null,
                    'button_radius' => $this->validated('theme_override_button_radius') ?: null,
                    'container_width' => $this->validated('theme_override_container_width') ?: null,
                    'section_spacing' => $this->validated('theme_override_section_spacing') ?: null,
                ],
            ],
        ];
    }
}
