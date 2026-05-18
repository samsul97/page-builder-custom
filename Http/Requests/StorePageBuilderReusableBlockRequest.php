<?php

namespace Modules\PageBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StorePageBuilderReusableBlockRequest extends FormRequest
{
    protected ?array $decodedBlocks = null;

    protected bool $blocksJsonInvalid = false;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $slugInput = trim((string) $this->input('slug', ''));
        $blocksJson = trim((string) $this->input('blocks_json', ''));

        $this->merge([
            'name' => $name,
            'slug' => Str::slug($slugInput !== '' ? $slugInput : $name),
            'is_active' => $this->boolean('is_active'),
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
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', 'unique:pb_reusable_blocks,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'blocks_json' => ['nullable', 'string'],
        ];
    }

    public function reusableBlockPayload(): array
    {
        return [
            'name' => $this->validated('name'),
            'slug' => $this->validated('slug'),
            'description' => $this->validated('description'),
            'is_active' => (bool) $this->validated('is_active', false),
            'blocks' => $this->decodedBlocks ?? [],
            'settings' => [],
        ];
    }
}
