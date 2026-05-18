<?php

namespace Modules\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageBuilderContentType extends Model
{
    protected $table = 'pb_content_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'schema',
        'is_active',
    ];

    protected $casts = [
        'schema' => 'array',
        'is_active' => 'boolean',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(PageBuilderContentEntry::class, 'content_type_id');
    }
}
