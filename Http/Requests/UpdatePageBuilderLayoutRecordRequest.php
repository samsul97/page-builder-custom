<?php

namespace Modules\PageBuilder\Http\Requests;

use App\Models\PageBuilder\PageBuilderLayout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePageBuilderLayoutRecordRequest extends StorePageBuilderLayoutRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $name = trim((string) $this->input('name', ''));
        $keyInput = trim((string) $this->input('key', ''));

        $this->merge([
            'name' => $name,
            'key' => Str::slug($keyInput !== '' ? $keyInput : $name),
        ]);
    }

    public function rules(): array
    {
        /** @var PageBuilderLayout|null $layout */
        $layout = $this->route('pageBuilderLayout');

        $rules = parent::rules();
        $rules['key'] = ['required', 'string', 'max:190', Rule::unique('pb_layouts', 'key')->ignore($layout?->id)];

        return $rules;
    }
}
