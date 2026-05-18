<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Http\Requests\StorePageBuilderLayoutRequest;
use Modules\PageBuilder\Http\Requests\UpdatePageBuilderLayoutRecordRequest;
use Modules\PageBuilder\Models\PageBuilderCoreLayout;
use Modules\PageBuilder\Models\PageBuilderLayout;
use Modules\PageBuilder\Models\PageBuilderPage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageBuilderLayoutController extends Controller
{
    public function index(): View
    {
        return view('pagebuilder::chrome-layouts.index', [
            'chromeLayouts' => PageBuilderLayout::query()
                ->where('key', '!=', 'default')
                ->with('coreLayout:id,name')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('pagebuilder::chrome-layouts.create', [
            'layout' => new PageBuilderLayout([
                'is_active' => true,
                'settings' => PageBuilderLayout::defaultSettings(),
                'core_layout_id' => PageBuilderCoreLayout::query()
                    ->where('is_active', true)
                    ->where('key', '!=', 'default')
                    ->orderBy('name')
                    ->value('id'),
            ]),
            'coreLayouts' => PageBuilderCoreLayout::query()
                ->where('is_active', true)
                ->where('key', '!=', 'default')
                ->orderBy('name')
                ->get(['id', 'name', 'settings']),
        ]);
    }

    public function store(StorePageBuilderLayoutRequest $request): RedirectResponse
    {
        $layout = PageBuilderLayout::create($request->layoutPayload());

        flash()->success('Chrome layout created successfully.');

        return redirect()->route('page-builder.chrome-layouts.edit', $layout);
    }

    public function edit(PageBuilderLayout $pageBuilderLayout): View
    {
        return view('pagebuilder::chrome-layouts.edit', [
            'layout' => $pageBuilderLayout,
            'presetKey' => data_get($pageBuilderLayout->settings, 'preset.key'),
            'coreLayouts' => PageBuilderCoreLayout::query()
                ->where('is_active', true)
                ->where('key', '!=', 'default')
                ->orderBy('name')
                ->get(['id', 'name', 'settings']),
        ]);
    }

    public function preview(Request $request): View
    {
        $coreLayout = $request->filled('core_layout_id')
            ? PageBuilderCoreLayout::query()->where('is_active', true)->find((int) $request->input('core_layout_id'))
            : null;

        $coreLayout ??= PageBuilderCoreLayout::defaultRecord();

        $layout = new PageBuilderLayout([
            'key' => 'preview',
            'name' => 'Preview Chrome Layout',
            'core_layout_id' => $coreLayout->id,
            'is_active' => true,
            'settings' => $this->previewLayoutSettings($request),
        ]);

        $page = new PageBuilderPage([
            'title' => 'Chrome Layout Preview',
            'slug' => 'chrome-layout-preview',
            'is_published' => false,
            'blocks' => [],
            'settings' => [
                'layout_mode' => PageBuilderPage::LAYOUT_MODE_INCLUDE,
                'core_layout_id' => $coreLayout->id,
                'chrome_layout_id' => null,
                'show_header' => true,
                'show_footer' => true,
                'content_mode' => PageBuilderPage::CONTENT_MODE_RAW_HTML,
                'raw_markup' => $this->previewMarkup((string) $request->input('preview_scene', 'landing')),
            ],
        ]);

        return view('pagebuilder::public.show', [
            'page' => $page,
            'blocks' => [],
            'layout' => $layout,
            'coreLayout' => $coreLayout,
            'dynamicEntries' => collect(),
            'forms' => collect(),
            'disablePageBuilderAds' => true,
            'isPageBuilderPreview' => true,
        ]);
    }

    public function update(UpdatePageBuilderLayoutRecordRequest $request, PageBuilderLayout $pageBuilderLayout): RedirectResponse
    {
        $pageBuilderLayout->update($request->layoutPayload());

        flash()->success('Chrome layout updated successfully.');

        return redirect()->route('page-builder.chrome-layouts.edit', $pageBuilderLayout);
    }

    public function destroy(PageBuilderLayout $pageBuilderLayout): RedirectResponse
    {
        $usedByPages = PageBuilderPage::query()
            ->where('settings->chrome_layout_id', $pageBuilderLayout->id)
            ->exists();

        if ($pageBuilderLayout->key === 'default' || $usedByPages) {
            flash()->error('Chrome layout cannot be deleted because it is default or still used by pages.');

            return redirect()->route('page-builder.chrome-layouts.index');
        }

        $pageBuilderLayout->delete();

        flash()->success('Chrome layout deleted successfully.');

        return redirect()->route('page-builder.chrome-layouts.index');
    }

    protected function previewLayoutSettings(Request $request): array
    {
        $parseJsonArray = function ($value): array {
            if (is_array($value)) {
                return $value;
            }

            $value = trim((string) $value);

            if ($value === '') {
                return [];
            }

            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        };

        $links = collect($request->input('header_links', []))
            ->map(function ($item) use ($parseJsonArray) {
                $children = collect($parseJsonArray(data_get($item, 'children_json')))
                    ->map(fn ($child) => [
                        'label' => trim((string) data_get($child, 'label')),
                        'url' => trim((string) data_get($child, 'url')),
                    ])
                    ->filter(fn ($child) => filled($child['label']) || filled($child['url']))
                    ->values()
                    ->all();

                $sections = collect($parseJsonArray(data_get($item, 'sections_json')))
                    ->map(function ($section) {
                        return [
                            'title' => trim((string) data_get($section, 'title')),
                            'links' => collect(data_get($section, 'links', []))
                                ->map(fn ($link) => [
                                    'label' => trim((string) data_get($link, 'label')),
                                    'url' => trim((string) data_get($link, 'url')),
                                ])
                                ->filter(fn ($link) => filled($link['label']) || filled($link['url']))
                                ->values()
                                ->all(),
                        ];
                    })
                    ->filter(fn ($section) => filled($section['title']) || count($section['links']) > 0)
                    ->values()
                    ->all();

                return [
                    'type' => trim((string) data_get($item, 'type', 'link')),
                    'label' => trim((string) data_get($item, 'label')),
                    'url' => trim((string) data_get($item, 'url')),
                    'children' => $children,
                    'sections' => $sections,
                ];
            })
            ->filter(fn ($item) => filled($item['label']) || filled($item['url']) || count($item['children']) > 0 || count($item['sections']) > 0)
            ->values()
            ->all();

        $simpleLinks = fn ($items) => collect($items)
            ->map(fn ($item) => [
                'label' => trim((string) data_get($item, 'label')),
                'url' => trim((string) data_get($item, 'url')),
            ])
            ->filter(fn ($item) => filled($item['label']) || filled($item['url']))
            ->values()
            ->all();

        $contacts = collect($request->input('footer_contacts', []))
            ->map(fn ($item) => [
                'label' => trim((string) data_get($item, 'label')),
                'phone' => trim((string) data_get($item, 'phone')),
            ])
            ->filter(fn ($item) => filled($item['label']) || filled($item['phone']))
            ->values()
            ->all();

        $locations = collect($request->input('footer_locations', []))
            ->map(function ($item) {
                return [
                    'label' => trim((string) data_get($item, 'label')),
                    'map_url' => trim((string) data_get($item, 'map_url')),
                    'lines' => collect(data_get($item, 'lines', []))->map(fn ($line) => trim((string) $line))->filter()->values()->all(),
                    'weekday_label' => trim((string) data_get($item, 'weekday_label')),
                    'weekday_value' => trim((string) data_get($item, 'weekday_value')),
                    'weekend_label' => trim((string) data_get($item, 'weekend_label')),
                    'weekend_value' => trim((string) data_get($item, 'weekend_value')),
                ];
            })
            ->filter(fn ($item) => filled($item['label']) || filled($item['map_url']) || count($item['lines']) > 0)
            ->values()
            ->all();

        return PageBuilderLayout::mergeSettings([
            'header' => [
                'variant' => trim((string) $request->input('header_variant', PageBuilderLayout::HEADER_VARIANT_CLASSIC)),
                'brand_name' => trim((string) $request->input('header_brand_name', 'Preview Brand')),
                'brand_logo_url' => trim((string) $request->input('header_brand_logo_url', '')) ?: null,
                'brand_logo_alt' => trim((string) $request->input('header_brand_logo_alt', 'Preview logo')) ?: null,
                'tagline' => trim((string) $request->input('header_tagline', '')) ?: null,
                'button_label' => trim((string) $request->input('header_button_label', '')) ?: null,
                'button_url' => trim((string) $request->input('header_button_url', '')) ?: null,
                'links' => $links,
            ],
            'navigation' => [
                'style' => trim((string) $request->input('navigation_style', 'inline')),
                'density' => trim((string) $request->input('navigation_density', 'comfortable')),
                'is_sticky' => filter_var($request->input('navigation_is_sticky', false), FILTER_VALIDATE_BOOL),
                'show_top_bar' => filter_var($request->input('navigation_show_top_bar', false), FILTER_VALIDATE_BOOL),
                'top_bar_text' => trim((string) $request->input('navigation_top_bar_text', '')) ?: null,
                'top_bar_link_label' => trim((string) $request->input('navigation_top_bar_link_label', '')) ?: null,
                'top_bar_link_url' => trim((string) $request->input('navigation_top_bar_link_url', '')) ?: null,
                'meta_label' => trim((string) $request->input('navigation_meta_label', '')) ?: null,
                'meta_value' => trim((string) $request->input('navigation_meta_value', '')) ?: null,
                'meta_url' => trim((string) $request->input('navigation_meta_url', '')) ?: null,
            ],
            'chrome_visual' => [
                'header_surface_style' => trim((string) $request->input('chrome_header_surface_style', 'glass')),
            ],
            'footer' => [
                'variant' => trim((string) $request->input('footer_variant', PageBuilderLayout::FOOTER_VARIANT_COLUMNS)),
                'surface_style' => trim((string) $request->input('footer_surface_style', 'dark')),
                'brand_title' => trim((string) $request->input('footer_brand_title', '')) ?: null,
                'brand_text' => trim((string) $request->input('footer_brand_text', '')) ?: null,
                'social_title' => trim((string) $request->input('footer_social_title', '')) ?: null,
                'social_links' => $simpleLinks($request->input('footer_social_links', [])),
                'journey_title' => trim((string) $request->input('footer_journey_title', '')) ?: null,
                'journey_links' => $simpleLinks($request->input('footer_journey_links', [])),
                'contact_title' => trim((string) $request->input('footer_contact_title', '')) ?: null,
                'contacts' => $contacts,
                'location_title' => trim((string) $request->input('footer_location_title', '')) ?: null,
                'hours_title' => trim((string) $request->input('footer_hours_title', '')) ?: null,
                'locations' => $locations,
                'bottom_origin' => trim((string) $request->input('footer_bottom_origin', '')) ?: null,
                'copyright' => trim((string) $request->input('footer_copyright', '')) ?: null,
            ],
        ]);
    }

    protected function previewMarkup(string $scene): string
    {
        $scene = in_array($scene, ['landing', 'story', 'minimal'], true) ? $scene : 'landing';

        return match ($scene) {
            'story' => <<<'HTML'
<section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
    <div class="text-uppercase small fw-semibold mb-3" style="letter-spacing: 0.14em; color: var(--pb-accent);">Brand Story</div>
    <h1 class="display-5 fw-bold mb-3">Preview the chrome around a calmer editorial page.</h1>
    <p class="pb-prose mb-0">Use this scene to check whether the header feels too dense, whether the footer closes the page cleanly, and whether announcement or meta navigation still feels balanced in a quieter layout.</p>
</section>
<section class="row g-4">
    <div class="col-lg-6">
        <div class="pb-section-card p-4 h-100">
            <h2 class="h4 mb-3">Header Readability</h2>
            <p class="pb-prose mb-0">Brand logo, nav items, and CTA should stay legible without overpowering content.</p>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="pb-section-card p-4 h-100">
            <h2 class="h4 mb-3">Footer Closure</h2>
            <p class="pb-prose mb-0">Footer titles, link density, and contact blocks should still feel organized when the page body is simple.</p>
        </div>
    </div>
</section>
HTML,
            'minimal' => <<<'HTML'
<section class="pb-section-card p-4 p-lg-5 text-center">
    <div class="text-uppercase small fw-semibold mb-3" style="letter-spacing: 0.14em; color: var(--pb-accent);">Minimal Scene</div>
    <h1 class="display-6 fw-bold mb-3">A stripped preview to inspect chrome spacing only.</h1>
    <p class="pb-prose mb-0">Use this when you want to evaluate just the breathing room between header, body, and footer.</p>
</section>
HTML,
            default => <<<'HTML'
<section class="pb-hero px-4 px-lg-5 py-5 py-lg-6 mb-4 mb-lg-5">
    <div class="row align-items-center g-4 position-relative" style="z-index: 1;">
        <div class="col-lg-8">
            <div class="text-uppercase small fw-semibold mb-3" style="letter-spacing: 0.18em; color: rgba(247, 243, 234, 0.7);">Landing Preview</div>
            <h1 class="display-4 fw-bold mb-3">See your header and footer inside a landing-page context.</h1>
            <p class="lead mb-4" style="max-width: 46rem; color: rgba(247, 243, 234, 0.82);">This preview scene is useful for checking CTA prominence, nav density, and how the chrome sits around marketing-style content.</p>
            <a href="#" class="btn btn-light btn-lg">Primary Call To Action</a>
        </div>
    </div>
</section>
<section class="row g-4">
    <div class="col-lg-4">
        <div class="pb-grid-card">
            <h2 class="h5 mb-3">CTA Weight</h2>
            <p class="pb-prose mb-0">Check whether the header button feels dominant enough without competing too hard with page content.</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="pb-grid-card">
            <h2 class="h5 mb-3">Menu Scan</h2>
            <p class="pb-prose mb-0">Check whether dropdowns, mega menus, and top bar content still scan quickly for first-time visitors.</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="pb-grid-card">
            <h2 class="h5 mb-3">Footer Density</h2>
            <p class="pb-prose mb-0">Check whether footer sections feel helpful or overloaded once the main landing content already carries a lot of information.</p>
        </div>
    </div>
</section>
HTML,
        };
    }
}
