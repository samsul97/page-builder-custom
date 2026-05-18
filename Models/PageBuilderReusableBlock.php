<?php

namespace Modules\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilderReusableBlock extends Model
{
    protected $table = 'pb_reusable_blocks';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'blocks',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'blocks' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
}
