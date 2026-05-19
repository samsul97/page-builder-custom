<?php

if (! function_exists('page_builder_enabled')) {
    function page_builder_enabled(): bool
    {
        // Nexcity / projects using Spatie Laravel Settings
        if (class_exists(\Modules\PageBuilder\Settings\PageBuilderSettings::class)) {
            try {
                return app(\Modules\PageBuilder\Settings\PageBuilderSettings::class)->enabled;
            } catch (\Exception $e) {
                // Settings table not ready (e.g. during fresh migration)
            }
        }

        // Projects using site_setting() helper (e.g. rawdee-glampings)
        if (function_exists('site_setting') && function_exists('to_boolean')) {
            return to_boolean(site_setting('page_builder_enabled', true)) !== false;
        }

        return true;
    }
}
