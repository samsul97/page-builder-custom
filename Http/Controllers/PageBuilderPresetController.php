<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Support\PageBuilderPresetCatalog;
use Modules\PageBuilder\Support\PageBuilderPresetInstantiator;
use Modules\PageBuilder\Support\PageBuilderLibraryCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class PageBuilderPresetController extends Controller
{
    public function __construct(
        protected PageBuilderPresetCatalog $catalog,
        protected PageBuilderPresetInstantiator $instantiator,
        protected PageBuilderLibraryCatalog $libraryCatalog,
    ) {
    }

    public function index(): View
    {
        return view('pagebuilder::presets.index', [
            'presets' => $this->catalog->all(),
        ]);
    }

    public function show(string $preset): View
    {
        $presetRecord = $this->catalog->findOrFail($preset);
        $relatedLibraryItems = $this->libraryCatalog->forPreset(data_get($presetRecord, 'key'));
        $enabledRelatedLibraryItems = $this->libraryCatalog->enabledForPreset(data_get($presetRecord, 'key'));
        $allLibraryItems = $this->libraryCatalog->all();
        $recommendedAssetKeys = collect(data_get($presetRecord, 'family.recommended_library_asset_keys', []))
            ->filter()
            ->values();
        $enabledAssetKeys = $allLibraryItems->where('status', 'enabled')->pluck('key');
        $missingRecommendedAssets = $recommendedAssetKeys
            ->reject(fn (string $key) => $enabledAssetKeys->contains($key))
            ->map(fn (string $key) => $allLibraryItems->firstWhere('key', $key) ?? ['key' => $key, 'name' => $key, 'status' => 'missing'])
            ->values();

        return view('pagebuilder::presets.show', [
            'preset' => $presetRecord,
            'allLibraryItems' => $allLibraryItems,
            'relatedLibraryItems' => $relatedLibraryItems,
            'enabledRelatedLibraryItems' => $enabledRelatedLibraryItems,
            'recommendedAssetKeys' => $recommendedAssetKeys,
            'missingRecommendedAssets' => $missingRecommendedAssets,
        ]);
    }

    public function instantiate(string $preset): RedirectResponse
    {
        $result = $this->instantiator->instantiate($preset);

        $createdPages = (int) data_get($result, 'created_pages_count', 0);
        $reusedPages = (int) data_get($result, 'reused_pages_count', 0);

        flash()->success(
            "Preset instantiated successfully. Created {$createdPages} starter page(s) and reused {$reusedPages} existing page(s)."
        );

        return redirect()
            ->route('page-builder.presets.show', $preset)
            ->with('presetInstantiation', [
                'core_layout' => [
                    'id' => data_get($result, 'core_layout.id'),
                    'name' => data_get($result, 'core_layout.name'),
                ],
                'chrome_layout' => [
                    'id' => data_get($result, 'chrome_layout.id'),
                    'name' => data_get($result, 'chrome_layout.name'),
                ],
                'pages' => collect(data_get($result, 'pages', []))
                    ->map(function (array $pageResult): array {
                        $page = data_get($pageResult, 'page');

                        return [
                            'status' => data_get($pageResult, 'status'),
                            'recipe_key' => data_get($pageResult, 'recipe_key'),
                            'title' => data_get($page, 'title'),
                            'slug' => data_get($page, 'slug'),
                            'edit_url' => $page ? route('page-builder.pages.edit', $page) : null,
                            'public_url' => $page && $page->is_published ? route('page-builder.public.show', $page) : null,
                        ];
                    })
                    ->all(),
            ]);
    }
}
