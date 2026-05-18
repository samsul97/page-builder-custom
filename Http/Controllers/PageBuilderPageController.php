<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Http\Requests\StorePageBuilderPageRequest;
use Modules\PageBuilder\Http\Requests\UpdatePageBuilderPageRequest;
use App\Models\Forms\Form;
use Modules\PageBuilder\Models\PageBuilderContentType;
use Modules\PageBuilder\Models\PageBuilderCoreLayout;
use Modules\PageBuilder\Models\PageBuilderLayout;
use Modules\PageBuilder\Models\PageBuilderPage;
use Modules\PageBuilder\Models\PageBuilderReusableBlock;
use Modules\PageBuilder\Support\PageBuilderBlockTypeRegistry;
use Modules\PageBuilder\Support\PageBuilderPresetCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageBuilderPageController extends Controller
{
    public function __construct(
        protected PageBuilderPresetCatalog $presetCatalog,
        protected PageBuilderBlockTypeRegistry $blockTypeRegistry,
    ) {
    }

    public function index(): View
    {
        return view('pagebuilder::pages.index', [
            'pages' => PageBuilderPage::query()->latest()->paginate(12),
            'coreLayouts' => PageBuilderCoreLayout::query()
                ->where('is_active', true)
                ->where('key', '!=', 'default')
                ->orderBy('name')
                ->get(['id', 'name', 'settings']),
            'chromeLayouts' => PageBuilderLayout::query()
                ->where('is_active', true)
                ->where('key', '!=', 'default')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('pagebuilder::pages.create', [
            'page' => new PageBuilderPage([
                'is_published' => false,
                'blocks' => [],
            ]),
            'presetContext' => null,
            'adsBuilderSettings' => [
                'meta_pixel_script' => site_setting('ads_builder_meta_pixel_script'),
                'meta_pixel_id' => site_setting('ads_builder_meta_pixel_id'),
                'meta_conversion_api_token' => site_setting('ads_builder_meta_conversion_api_token'),
                'meta_conversion_api_test_event_code' => site_setting('ads_builder_meta_conversion_api_test_event_code'),
                'tiktok_pixel_script' => site_setting('ads_builder_tiktok_pixel_script'),
                'google_analytics_script' => site_setting('ads_builder_google_analytics_script'),
            ],
            'reusableBlocks' => PageBuilderReusableBlock::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description', 'blocks']),
            'contentTypes' => PageBuilderContentType::query()
                ->where('is_active', true)
                ->withCount('entries')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description']),
            'forms' => Form::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'blockTypes' => $this->blockTypeRegistry->enabled(),
            'disabledBlockTypesUsed' => [],
            'coreLayouts' => PageBuilderCoreLayout::query()
                ->where('is_active', true)
                ->where('key', '!=', 'default')
                ->orderBy('name')
                ->get(['id', 'name', 'settings']),
            'chromeLayouts' => PageBuilderLayout::query()
                ->where('is_active', true)
                ->where('key', '!=', 'default')
                ->orderBy('name')
                ->get(['id', 'name', 'core_layout_id']),
        ]);
    }

    public function store(StorePageBuilderPageRequest $request): RedirectResponse
    {
        $page = PageBuilderPage::create($request->pagePayload());

        flash()->success('Builder page created successfully.');

        return redirect()->route('page-builder.pages.edit', $page);
    }

    public function edit(PageBuilderPage $pageBuilderPage): View
    {
        $pageSettings = $pageBuilderPage->mergedSettings();
        $presetKey = data_get($pageSettings, 'preset.key');

        return view('pagebuilder::pages.edit', [
            'page' => $pageBuilderPage,
            'publicUrl' => $pageBuilderPage->is_published
                ? route('page-builder.public.show', $pageBuilderPage)
                : null,
            'presetContext' => $presetKey ? $this->buildPresetContext($pageBuilderPage, $presetKey, $pageSettings) : null,
            'adsBuilderSettings' => [
                'meta_pixel_script' => site_setting('ads_builder_meta_pixel_script'),
                'meta_pixel_id' => site_setting('ads_builder_meta_pixel_id'),
                'meta_conversion_api_token' => site_setting('ads_builder_meta_conversion_api_token'),
                'meta_conversion_api_test_event_code' => site_setting('ads_builder_meta_conversion_api_test_event_code'),
                'tiktok_pixel_script' => site_setting('ads_builder_tiktok_pixel_script'),
                'google_analytics_script' => site_setting('ads_builder_google_analytics_script'),
            ],
            'reusableBlocks' => PageBuilderReusableBlock::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description', 'blocks']),
            'contentTypes' => PageBuilderContentType::query()
                ->where('is_active', true)
                ->withCount('entries')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description']),
            'forms' => Form::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'blockTypes' => $this->blockTypeRegistry->enabled(),
            'disabledBlockTypesUsed' => $this->blockTypeRegistry->usedDisabledTypes($pageBuilderPage->blocks ?? []),
            'coreLayouts' => PageBuilderCoreLayout::query()
                ->where('is_active', true)
                ->where('key', '!=', 'default')
                ->orderBy('name')
                ->get(['id', 'name', 'settings']),
            'chromeLayouts' => PageBuilderLayout::query()
                ->where('is_active', true)
                ->where('key', '!=', 'default')
                ->orderBy('name')
                ->get(['id', 'name', 'core_layout_id']),
        ]);
    }

    public function update(UpdatePageBuilderPageRequest $request, PageBuilderPage $pageBuilderPage): RedirectResponse
    {
        $pageBuilderPage->update($request->pagePayload());

        flash()->success('Builder page updated successfully.');

        return redirect()->route('page-builder.pages.edit', $pageBuilderPage);
    }

    public function destroy(PageBuilderPage $pageBuilderPage): RedirectResponse
    {
        $pageBuilderPage->delete();

        flash()->success('Builder page deleted successfully.');

        return redirect()->route('page-builder.pages.index');
    }

    protected function buildPresetContext(PageBuilderPage $pageBuilderPage, string $presetKey, array $pageSettings): ?array
    {
        try {
            $preset = $this->presetCatalog->findOrFail($presetKey);
        } catch (\Throwable) {
            return [
                'preset' => [
                    'key' => $presetKey,
                    'name' => data_get($pageSettings, 'preset.name', $presetKey),
                ],
                'starter_page' => [
                    'key' => data_get($pageSettings, 'preset.starter_page_key'),
                    'name' => data_get($pageSettings, 'preset.starter_page_name'),
                ],
                'recipe' => data_get($pageSettings, 'starter_recipe', []),
                'preset_url' => null,
            ];
        }

        return [
            'preset' => [
                'key' => data_get($preset, 'key'),
                'name' => data_get($preset, 'name'),
                'family_name' => data_get($preset, 'family.theme_name'),
            ],
            'starter_page' => [
                'key' => data_get($pageSettings, 'preset.starter_page_key'),
                'name' => data_get($pageSettings, 'preset.starter_page_name'),
            ],
            'recipe' => data_get($pageSettings, 'starter_recipe', []),
            'library_assets' => data_get($pageSettings, 'library_assets', []),
            'future_controls' => data_get($preset, 'blueprint.future_controls', []),
            'preset_url' => route('page-builder.presets.show', data_get($preset, 'key')),
            'core_layout_edit_url' => filled(data_get($pageSettings, 'core_layout_id'))
                ? route('page-builder.core-layouts.edit', data_get($pageSettings, 'core_layout_id'))
                : null,
            'chrome_layout_edit_url' => filled(data_get($pageSettings, 'chrome_layout_id'))
                ? route('page-builder.chrome-layouts.edit', data_get($pageSettings, 'chrome_layout_id'))
                : null,
        ];
    }
}
