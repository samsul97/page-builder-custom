<?php

namespace Modules\PageBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StorePageBuilderContentEntryRequest extends FormRequest
{
    protected ?array $decodedData = null;

    protected bool $dataJsonInvalid = false;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $title = trim((string) $this->input('title', ''));
        $slugInput = trim((string) $this->input('slug', ''));
        $dataJson = trim((string) $this->input('data_json', ''));
        $contentFields = $this->normalizeContentFields($this->input('content_fields', []));
        $extraContentFields = $this->normalizeExtraContentFields($this->input('extra_content_fields', []));

        $this->merge([
            'title' => $title,
            'slug' => Str::slug($slugInput !== '' ? $slugInput : $title),
            'is_published' => $this->boolean('is_published'),
        ]);

        $mergedFields = array_merge($contentFields, $extraContentFields);

        if (! empty($mergedFields)) {
            $this->decodedData = $mergedFields;

            return;
        }

        if ($dataJson === '') {
            $this->decodedData = [];

            return;
        }

        $decoded = json_decode($dataJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $this->dataJsonInvalid = true;

            return;
        }

        $this->decodedData = $decoded;
    }

    protected function normalizeContentFields(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $key => $fieldValue) {
            if (is_array($fieldValue)) {
                $nested = $this->normalizeContentFields($fieldValue);

                if ($nested !== []) {
                    $normalized[$key] = $nested;
                }

                continue;
            }

            if (is_bool($fieldValue) || is_numeric($fieldValue)) {
                $normalized[$key] = $fieldValue;

                continue;
            }

            $trimmed = trim((string) $fieldValue);

            if ($trimmed !== '') {
                $normalized[$key] = $trimmed;
            }
        }

        return $normalized;
    }

    protected function normalizeExtraContentFields(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $row) {
            $key = trim((string) data_get($row, 'key'));
            $rawValue = data_get($row, 'value');

            if ($key === '') {
                continue;
            }

            if (is_string($rawValue)) {
                $trimmed = trim($rawValue);

                if ($trimmed === '') {
                    continue;
                }

                if (
                    (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}'))
                    || (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
                ) {
                    $decoded = json_decode($trimmed, true);
                    $normalized[$key] = json_last_error() === JSON_ERROR_NONE ? $decoded : $trimmed;
                    continue;
                }

                $normalized[$key] = $trimmed;
                continue;
            }

            if ($rawValue !== null && $rawValue !== '') {
                $normalized[$key] = $rawValue;
            }
        }

        return $normalized;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->dataJsonInvalid) {
                $validator->errors()->add('data_json', 'Data JSON must be valid JSON object or array.');
            }
        });
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190'],
            'is_published' => ['sometimes', 'boolean'],
            'content_fields' => ['nullable', 'array'],
            'extra_content_fields' => ['nullable', 'array'],
            'data_json' => ['nullable', 'string'],
        ];
    }

    public function contentEntryPayload(): array
    {
        return [
            'title' => $this->validated('title'),
            'slug' => $this->validated('slug'),
            'data' => $this->decodedData ?? [],
            'is_published' => (bool) $this->validated('is_published', false),
        ];
    }
}
