<?php

namespace Modules\PageBuilder\Settings;

use Spatie\LaravelSettings\Settings;

class PageBuilderSettings extends Settings
{
    public bool $enabled;

    public static function group(): string
    {
        return 'page_builder';
    }
}
