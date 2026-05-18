<?php

namespace Modules\PageBuilder\Http\Requests;

use Illuminate\Validation\Rule;

class UpdatePageBuilderReusableBlockRequest extends StorePageBuilderReusableBlockRequest
{
    public function rules(): array
    {
        $reusableBlockId = $this->route('pageBuilderReusableBlock')?->id;

        return [
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', Rule::unique('pb_reusable_blocks', 'slug')->ignore($reusableBlockId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'blocks_json' => ['nullable', 'string'],
        ];
    }
}
