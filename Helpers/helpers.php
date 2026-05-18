<?php

if (! function_exists('page_builder_enabled')) {
    function page_builder_enabled(): bool
    {
        return to_boolean(site_setting('page_builder_enabled', true)) !== false;
    }
}
