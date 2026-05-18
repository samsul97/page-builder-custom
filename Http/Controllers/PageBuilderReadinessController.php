<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Models\PageBuilderCoreLayout;
use Modules\PageBuilder\Models\PageBuilderLayout;
use Modules\PageBuilder\Models\PageBuilderPage;
use App\Models\SiteSetting;
use Modules\PageBuilder\Support\PageBuilderLibraryCatalog;
use Modules\PageBuilder\Support\PageBuilderPresetCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageBuilderReadinessController extends Controller
{
    public function __invoke(PageBuilderPresetCatalog $presetCatalog, PageBuilderLibraryCatalog $libraryCatalog): View
    {
        $presets = $presetCatalog->all();
        $libraryItems = $libraryCatalog->all();
        $userCoreLayouts = PageBuilderCoreLayout::query()->where('key', '!=', 'default');
        $userChromeLayouts = PageBuilderLayout::query()->where('key', '!=', 'default');

        $requiredRoutes = [
            'Public landing page' => 'page-builder.public.show',
            'Page preview' => 'page-builder.pages.preview',
            'Core preview' => 'page-builder.core-layouts.preview',
            'Chrome preview' => 'page-builder.chrome-layouts.preview',
            'Preset catalog' => 'page-builder.presets.index',
            'Plugins / Theme' => 'page-builder.plugins-theme.index',
            'Ads Builder settings' => 'site-settings.ads-builder.edit',
        ];

        $ads = [
            'Meta Pixel Script' => filled(site_setting('ads_builder_meta_pixel_script')),
            'Meta CAPI Pixel ID' => filled(site_setting('ads_builder_meta_pixel_id')),
            'Meta CAPI Token' => filled(site_setting('ads_builder_meta_conversion_api_token')),
            'TikTok Pixel Script' => filled(site_setting('ads_builder_tiktok_pixel_script')),
            'Google Analytics Script' => filled(site_setting('ads_builder_google_analytics_script')),
        ];

        $enabledLibraryKeys = $libraryItems
            ->where('status', 'enabled')
            ->pluck('key')
            ->all();

        $recommendedAssetIssues = $presets
            ->flatMap(function (array $preset) use ($enabledLibraryKeys) {
                $presetKey = (string) data_get($preset, 'key');
                $recommendedKeys = collect(data_get($preset, 'family.recommended_library_asset_keys', []));

                return $recommendedKeys
                    ->reject(fn ($key) => in_array($key, $enabledLibraryKeys, true))
                    ->map(fn ($key) => [
                        'preset_key' => $presetKey,
                        'preset_name' => data_get($preset, 'name', $presetKey),
                        'asset_key' => $key,
                    ]);
            })
            ->values();

        $checklist = [
            [
                'label' => 'Page Builder enabled',
                'ready' => page_builder_enabled(),
                'detail' => 'Main feature flag for the admin and public builder routes.',
                'url' => route('site-settings.page-builder.edit'),
            ],
            [
                'label' => 'At least one preset exists',
                'ready' => $presets->isNotEmpty(),
                'detail' => $presets->count() . ' preset(s) registered.',
                'url' => route('page-builder.presets.index'),
            ],
            [
                'label' => 'Core layouts available',
                'ready' => (clone $userCoreLayouts)->where('is_active', true)->exists(),
                'detail' => (clone $userCoreLayouts)->where('is_active', true)->count() . ' active user-facing core layout(s).',
                'url' => route('page-builder.core-layouts.index'),
            ],
            [
                'label' => 'Chrome layouts available',
                'ready' => (clone $userChromeLayouts)->where('is_active', true)->exists(),
                'detail' => (clone $userChromeLayouts)->where('is_active', true)->count() . ' active user-facing chrome layout(s).',
                'url' => route('page-builder.chrome-layouts.index'),
            ],
            [
                'label' => 'Builder ads configured',
                'ready' => collect($ads)->contains(true),
                'detail' => collect($ads)->filter()->count() . ' of ' . count($ads) . ' ads fields are filled.',
                'url' => route('site-settings.ads-builder.edit'),
            ],
            [
                'label' => 'Recommended preset assets enabled',
                'ready' => $recommendedAssetIssues->isEmpty(),
                'detail' => $recommendedAssetIssues->isEmpty()
                    ? 'All recommended preset assets are enabled.'
                    : $recommendedAssetIssues->count() . ' recommended asset(s) are missing or disabled.',
                'url' => route('page-builder.plugins-theme.index'),
            ],
            [
                'label' => 'Preview routes registered',
                'ready' => collect($requiredRoutes)->every(fn ($routeName) => Route::has($routeName)),
                'detail' => collect($requiredRoutes)->filter(fn ($routeName) => Route::has($routeName))->count() . ' of ' . count($requiredRoutes) . ' required routes exist.',
                'url' => route('page-builder.pages.index'),
            ],
        ];

        $manualTestStates = $this->manualTestStates();
        $manualTests = collect($this->manualTestDefinitions())
            ->map(function (array $test) use ($manualTestStates): array {
                $state = $manualTestStates[data_get($test, 'key')] ?? [];

                return [
                    ...$test,
                    'status' => data_get($state, 'status', 'pending'),
                    'notes' => data_get($state, 'notes'),
                    'checked_at' => data_get($state, 'checked_at'),
                ];
            })
            ->values();

        return view('pagebuilder::readiness.index', [
            'checklist' => $checklist,
            'manualTests' => $manualTests,
            'manualTestSummary' => [
                'total' => $manualTests->count(),
                'passed' => $manualTests->where('status', 'pass')->count(),
                'failed' => $manualTests->where('status', 'fail')->count(),
                'pending' => $manualTests->where('status', 'pending')->count(),
            ],
            'routeChecks' => collect($requiredRoutes)
                ->map(fn ($routeName, $label) => [
                    'label' => $label,
                    'route' => $routeName,
                    'ready' => Route::has($routeName),
                    'url' => Route::has($routeName) && ! str_contains($routeName, 'public.show') && ! str_contains($routeName, 'preview')
                        ? route($routeName)
                        : null,
                ])
                ->values(),
            'ads' => $ads,
            'counts' => [
                'pages' => PageBuilderPage::query()->count(),
                'published_pages' => PageBuilderPage::query()->where('is_published', true)->count(),
                'draft_pages' => PageBuilderPage::query()->where('is_published', false)->count(),
                'core_layouts' => (clone $userCoreLayouts)->count(),
                'chrome_layouts' => (clone $userChromeLayouts)->count(),
                'presets' => $presets->count(),
                'library_items' => $libraryItems->count(),
                'enabled_library_items' => $libraryItems->where('status', 'enabled')->count(),
            ],
            'recommendedAssetIssues' => $recommendedAssetIssues,
        ]);
    }

    public function updateManualTest(Request $request): RedirectResponse
    {
        $testKeys = collect($this->manualTestDefinitions())->pluck('key')->all();

        $validated = $request->validate([
            'test_key' => ['required', 'string', Rule::in($testKeys)],
            'status' => ['required', 'string', Rule::in(['pending', 'pass', 'fail'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $states = $this->manualTestStates();
        $states[$validated['test_key']] = [
            'status' => $validated['status'],
            'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
            'checked_at' => now()->toDateTimeString(),
        ];

        SiteSetting::query()->updateOrCreate(
            ['key' => 'page_builder_readiness_manual_tests'],
            ['value' => json_encode($states, JSON_UNESCAPED_SLASHES)]
        );

        site_setting_forget_cache();

        flash()->success('Manual test status updated.');

        return redirect()->route('page-builder.readiness.index');
    }

    protected function manualTestStates(): array
    {
        $raw = site_setting('page_builder_readiness_manual_tests', []);

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    protected function manualTestDefinitions(): array
    {
        return [
            [
                'key' => 'page-preview',
                'name' => 'Test Page Preview',
                'area' => 'Preview',
                'description' => 'Open a builder page draft, change layout mode, content mode, core/chrome layout, raw HTML, and block JSON, then confirm preview updates without ads firing.',
                'url' => route('page-builder.pages.index'),
            ],
            [
                'key' => 'core-preview',
                'name' => 'Test Core Preview',
                'area' => 'Preview',
                'description' => 'Open a core layout, switch preview scenes and viewports, adjust fonts/colors/spacing, and confirm the theme-aware preview follows the active preset context.',
                'url' => route('page-builder.core-layouts.index'),
            ],
            [
                'key' => 'chrome-preview',
                'name' => 'Test Chrome Preview',
                'area' => 'Preview',
                'description' => 'Open a chrome layout, expand preview, test desktop navigation, dropdown/megamenu visibility, header/footer surfaces, and mobile toggler behavior.',
                'url' => route('page-builder.chrome-layouts.index'),
            ],
            [
                'key' => 'ads-builder',
                'name' => 'Test Ads Builder',
                'area' => 'Tracking',
                'description' => 'Confirm landing pages use ads-builder scripts while editor preview keeps ads and Meta CAPI disabled.',
                'url' => route('site-settings.ads-builder.edit'),
            ],
            [
                'key' => 'preset-instantiate',
                'name' => 'Test Preset Instantiate',
                'area' => 'Template',
                'description' => 'Instantiate the baseline preset and confirm it creates or reuses core layout, chrome layout, and starter draft pages without overwriting existing drafts.',
                'url' => route('page-builder.presets.index'),
            ],
            [
                'key' => 'plugins-theme-state',
                'name' => 'Test Plugins / Theme State',
                'area' => 'Library',
                'description' => 'Enable and disable related library assets, then confirm preset detail and readiness warnings reflect the state.',
                'url' => route('page-builder.plugins-theme.index'),
            ],
            [
                'key' => 'public-landing-page',
                'name' => 'Test Public Landing Page',
                'area' => 'Public',
                'description' => 'Publish a draft and open the public landing page URL to confirm chrome, SEO, ads-builder, and responsive behavior outside the editor.',
                'url' => route('page-builder.pages.index'),
            ],
        ];
    }
}
