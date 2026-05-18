<?php

namespace Modules\PageBuilder\Http\Requests;

use Illuminate\Validation\Rule;

class UpdatePageBuilderContentTypeRequest extends StorePageBuilderContentTypeRequest
{
    public function rules(): array
    {
        $contentTypeId = $this->route('pageBuilderContentType')?->id;

        return [
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', Rule::unique('pb_content_types', 'slug')->ignore($contentTypeId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'schema_fields' => ['nullable', 'array'],
            'schema_json' => ['nullable', 'string'],
        ];
    }
}
