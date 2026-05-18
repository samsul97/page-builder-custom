<?php

namespace Modules\PageBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StorePageBuilderContentTypeRequest extends FormRequest
{
    protected ?array $decodedSchema = null;

    protected bool $schemaJsonInvalid = false;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $slugInput = trim((string) $this->input('slug', ''));
        $schemaJson = trim((string) $this->input('schema_json', ''));
        $schemaFields = $this->normalizeSchemaFields($this->input('schema_fields', []));

        $this->merge([
            'name' => $name,
            'slug' => Str::slug($slugInput !== '' ? $slugInput : $name),
            'is_active' => $this->boolean('is_active'),
        ]);

        if (! empty($schemaFields)) {
            $this->decodedSchema = $schemaFields;

            return;
        }

        if ($schemaJson === '') {
            $this->decodedSchema = [];

            return;
        }

        $decoded = json_decode($schemaJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $this->schemaJsonInvalid = true;

            return;
        }

        $this->decodedSchema = $decoded;
    }

    protected function normalizeSchemaFields(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(function ($field) {
                $options = collect(explode("\n", (string) data_get($field, 'options')))
                    ->map(fn ($item) => trim($item))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'name' => trim((string) data_get($field, 'name')),
                    'type' => trim((string) data_get($field, 'type', 'text')),
                    'label' => trim((string) data_get($field, 'label')),
                    'placeholder' => trim((string) data_get($field, 'placeholder')),
                    'help_text' => trim((string) data_get($field, 'help_text')),
                    'options' => $options,
                ];
            })
            ->filter(fn ($field) => filled($field['name']) || filled($field['label']))
            ->map(function ($field) {
                if (empty($field['options'])) {
                    unset($field['options']);
                }

                return array_filter($field, fn ($value) => $value !== '');
            })
            ->values()
            ->all();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->schemaJsonInvalid) {
                $validator->errors()->add('schema_json', 'Schema JSON must be a valid JSON array.');
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', 'unique:pb_content_types,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'schema_fields' => ['nullable', 'array'],
            'schema_json' => ['nullable', 'string'],
        ];
    }

    public function contentTypePayload(): array
    {
        return [
            'name' => $this->validated('name'),
            'slug' => $this->validated('slug'),
            'description' => $this->validated('description'),
            'schema' => $this->decodedSchema ?? [],
            'is_active' => (bool) $this->validated('is_active', false),
        ];
    }
}
