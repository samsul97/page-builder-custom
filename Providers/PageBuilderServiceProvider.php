<?php

namespace Modules\PageBuilder\Providers;

use Illuminate\Support\ServiceProvider;

class PageBuilderServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'PageBuilder';
    protected string $moduleNameLower = 'pagebuilder';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $configPath = module_path($this->moduleName, 'Config');

        foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->mergeConfigFrom($file, $key);
            $this->publishes([$file => config_path(basename($file))], 'config');
        }
    }

    public function registerViews(): void
    {
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    public function provides(): array
    {
        return [];
    }
}
