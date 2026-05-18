<?php

namespace Modules\PageBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPageBuilderMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/svg+xml', 'max:4096'],
        ];
    }
}
