<?php

namespace Modules\PageBuilder\Support;

use Modules\PageBuilder\Models\PageBuilderCoreLayout;
use Modules\PageBuilder\Models\PageBuilderLayout;
use Modules\PageBuilder\Models\PageBuilderPage;
use Illuminate\Support\Str;

class PageBuilderPresetInstantiator
{
    public function __construct(
        protected PageBuilderPresetCatalog $catalog,
        protected PageBuilderLibraryCatalog $libraryCatalog,
    ) {
    }

    public function instantiate(string $presetKey): array
    {
        $preset = $this->catalog->findOrFail($presetKey);

        $coreLayoutBlueprint = data_get($preset, 'blueprint.core_layout', []);
        $chromeLayoutBlueprint = data_get($preset, 'blueprint.chrome_layout', []);
        $starterPages = data_get($preset, 'blueprint.starter_pages', []);
        $enabledLibraryItems = $this->libraryCatalog
            ->enabledForPreset(data_get($preset, 'key'))
            ->map(fn (array $item) => $this->mapLibraryAsset($item))
            ->all();

        $coreLayout = $this->upsertCoreLayout($preset, $coreLayoutBlueprint, $enabledLibraryItems);
        $chromeLayout = $this->upsertChromeLayout($preset, $chromeLayoutBlueprint, $coreLayout, $enabledLibraryItems);

        $pageResults = collect($starterPages)
            ->map(fn (array $recipe) => $this->upsertStarterPage($preset, $recipe, $coreLayout, $chromeLayout, $enabledLibraryItems))
            ->all();

        return [
            'preset' => $preset,
            'core_layout' => $coreLayout,
            'chrome_layout' => $chromeLayout,
            'pages' => $pageResults,
            'enabled_library_items' => $enabledLibraryItems,
            'created_pages_count' => collect($pageResults)->where('status', 'created')->count(),
            'reused_pages_count' => collect($pageResults)->where('status', 'reused')->count(),
        ];
    }

    protected function upsertCoreLayout(array $preset, array $blueprint, array $enabledLibraryItems): PageBuilderCoreLayout
    {
        $attributes = ['key' => data_get($blueprint, 'key')];
        $settings = PageBuilderCoreLayout::mergeSettings(data_get($blueprint, 'settings', []));
        $settings['preset'] = [
            'key' => data_get($preset, 'key'),
            'name' => data_get($preset, 'name'),
        ];
        $settings['library_assets'] = $enabledLibraryItems;

        $values = [
            'name' => data_get($blueprint, 'name', 'Preset Core Layout'),
            'settings' => $settings,
            'is_active' => true,
        ];

        /** @var PageBuilderCoreLayout $layout */
        $layout = PageBuilderCoreLayout::query()->updateOrCreate($attributes, $values);

        return $layout;
    }

    protected function upsertChromeLayout(array $preset, array $blueprint, PageBuilderCoreLayout $coreLayout, array $enabledLibraryItems): PageBuilderLayout
    {
        $settings = PageBuilderLayout::mergeSettings(data_get($blueprint, 'settings', []));
        $settings['preset'] = [
            'key' => data_get($preset, 'key'),
            'name' => data_get($preset, 'name'),
        ];
        $settings['library_assets'] = $enabledLibraryItems;

        /** @var PageBuilderLayout $layout */
        $layout = PageBuilderLayout::query()->updateOrCreate(
            ['key' => data_get($blueprint, 'key')],
            [
                'name' => data_get($blueprint, 'name', 'Preset Chrome Layout'),
                'core_layout_id' => $coreLayout->id,
                'settings' => $settings,
                'is_active' => true,
            ]
        );

        return $layout;
    }

    protected function upsertStarterPage(
        array $preset,
        array $recipe,
        PageBuilderCoreLayout $coreLayout,
        PageBuilderLayout $chromeLayout,
        array $enabledLibraryItems
    ): array {
        $slug = Str::slug(data_get($preset, 'key') . '-' . data_get($recipe, 'key'));
        $existing = PageBuilderPage::query()->where('slug', $slug)->first();

        if ($existing) {
            return [
                'status' => 'reused',
                'page' => $existing,
                'slug' => $slug,
                'recipe_key' => data_get($recipe, 'key'),
            ];
        }

        $pageSettings = [
            ...PageBuilderPage::defaultSettings(),
            ...data_get($recipe, 'page_settings', []),
            'core_layout_id' => $coreLayout->id,
            'chrome_layout_id' => data_get($recipe, 'page_settings.layout_mode', PageBuilderPage::LAYOUT_MODE_INCLUDE) === PageBuilderPage::LAYOUT_MODE_INCLUDE
                ? $chromeLayout->id
                : null,
            'preset' => [
                'key' => data_get($preset, 'key'),
                'name' => data_get($preset, 'name'),
                'starter_page_key' => data_get($recipe, 'key'),
                'starter_page_name' => data_get($recipe, 'name'),
            ],
            'starter_recipe' => [
                'purpose' => data_get($recipe, 'purpose'),
                'block_recipe' => data_get($recipe, 'block_recipe', []),
            ],
            'library_assets' => $enabledLibraryItems,
        ];

        /** @var PageBuilderPage $page */
        $page = PageBuilderPage::query()->create([
            'title' => data_get($recipe, 'name', 'Preset Starter Page'),
            'slug' => $slug,
            'meta_title' => data_get($recipe, 'name', 'Preset Starter Page'),
            'meta_description' => data_get($recipe, 'purpose'),
            'meta_keywords' => null,
            'og_image_path' => null,
            'is_published' => false,
            'blocks' => [],
            'settings' => $pageSettings,
        ]);

        return [
            'status' => 'created',
            'page' => $page,
            'slug' => $slug,
            'recipe_key' => data_get($recipe, 'key'),
        ];
    }

    protected function mapLibraryAsset(array $item): array
    {
        return [
            'key' => data_get($item, 'key'),
            'name' => data_get($item, 'name'),
            'type' => data_get($item, 'type'),
            'category' => data_get($item, 'category'),
            'status' => data_get($item, 'status'),
            'source' => data_get($item, 'source'),
        ];
    }
}
