<?php

namespace Modules\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBuilderContentEntry extends Model
{
    protected $table = 'pb_content_entries';

    protected $fillable = [
        'content_type_id',
        'title',
        'slug',
        'data',
        'is_published',
    ];

    protected $casts = [
        'data' => 'array',
        'is_published' => 'boolean',
    ];

    public function contentType(): BelongsTo
    {
        return $this->belongsTo(PageBuilderContentType::class, 'content_type_id');
    }
}
