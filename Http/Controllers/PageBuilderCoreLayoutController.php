<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Http\Requests\StorePageBuilderCoreLayoutRequest;
use Modules\PageBuilder\Http\Requests\UpdatePageBuilderCoreLayoutRequest;
use Modules\PageBuilder\Models\PageBuilderCoreLayout;
use Modules\PageBuilder\Models\PageBuilderLayout;
use Modules\PageBuilder\Models\PageBuilderPage;
use Modules\PageBuilder\Support\PageBuilderPresetCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageBuilderCoreLayoutController extends Controller
{
    public function index(): View
    {
        return view('pagebuilder::core-layouts.index', [
            'coreLayouts' => PageBuilderCoreLayout::query()
                ->where('key', '!=', 'default')
                ->withCount('chromeLayouts')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('pagebuilder::core-layouts.create', [
            'coreLayout' => new PageBuilderCoreLayout([
                'is_active' => true,
                'settings' => PageBuilderCoreLayout::defaultSettings(),
            ]),
            'presetKey' => null,
        ]);
    }

    public function store(StorePageBuilderCoreLayoutRequest $request): RedirectResponse
    {
        $coreLayout = PageBuilderCoreLayout::create($request->coreLayoutPayload());

        flash()->success('Core layout created successfully.');

        return redirect()->route('page-builder.core-layouts.edit', $coreLayout);
    }

    public function edit(PageBuilderCoreLayout $pageBuilderCoreLayout): View
    {
        return view('pagebuilder::core-layouts.edit', [
            'coreLayout' => $pageBuilderCoreLayout,
            'presetKey' => data_get($pageBuilderCoreLayout->settings, 'preset.key'),
        ]);
    }

    public function preview(Request $request): View
    {
        $previewScene = (string) $request->input('preview_scene', 'landing');
        $presetKey = trim((string) $request->input('preview_preset_key', ''));
        $preset = null;

        if ($presetKey !== '') {
            try {
                $preset = app(PageBuilderPresetCatalog::class)->findOrFail($presetKey);
            } catch (\Throwable) {
                $preset = null;
            }
        }

        $previewMarkup = match ($previewScene) {
            'story' => <<<'HTML'
<section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
    <div class="text-uppercase small fw-semibold mb-3" style="letter-spacing: 0.14em; color: var(--pb-accent);">Story Preview</div>
    <h1 class="display-5 fw-bold mb-3">A calmer editorial scene helps you judge body readability and heading tone.</h1>
    <p class="pb-prose mb-4">Use this mode when you need to inspect serif pairings, muted text contrast, and whether longer paragraphs still feel comfortable across the chosen container width.</p>
    <div class="border-top pt-4 mt-4">
        <p class="small text-uppercase fw-semibold mb-2" style="letter-spacing: 0.14em; color: var(--pb-accent);">Editor Note</p>
        <p class="pb-prose mb-0">Typography should still feel deliberate when content becomes more narrative and less card-driven.</p>
    </div>
</section>
<section class="row g-4">
    <div class="col-lg-8">
        <div class="pb-grid-card h-100">
            <h2 class="h3 mb-3">Long-form Paragraph Sample</h2>
            <p class="pb-prose">A strong core layout should not only look good in short landing-page bursts. It should also hold up when the content becomes slower, more descriptive, and more text-heavy. This sample helps evaluate body rhythm, line length, and how muted copy behaves on the current surface colors.</p>
            <p class="pb-prose mb-0">If the font pairing feels too sharp, too ornamental, or too flat here, it will usually show up faster in this story scene than in a hero-heavy landing composition.</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="pb-grid-card h-100">
            <h2 class="h5 mb-3">Pullout</h2>
            <p class="pb-prose mb-0">Accent, card color, and heading family should still feel balanced when the layout becomes more editorial than promotional.</p>
        </div>
    </div>
</section>
HTML,
            'minimal' => <<<'HTML'
<section class="py-5 text-center">
    <div class="text-uppercase small fw-semibold mb-3" style="letter-spacing: 0.14em; color: var(--pb-accent);">Minimal Preview</div>
    <h1 class="display-5 fw-bold mb-3">A stripped-down scene makes spacing and typography issues easier to spot.</h1>
    <p class="pb-prose mx-auto mb-4" style="max-width: 42rem;">Use this when you want the least visual noise and the clearest read on base font family, heading family, accent usage, and global spacing rhythm.</p>
    <div class="d-inline-flex flex-wrap justify-content-center gap-3">
        <a href="#" class="btn pb-btn-accent">Primary Button</a>
        <a href="#" class="pb-social-chip">Neutral Surface</a>
    </div>
</section>
HTML,
            default => <<<'HTML'
<section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
    <div class="text-uppercase small fw-semibold mb-3" style="letter-spacing: 0.14em; color: var(--pb-accent);">Core Layout Preview</div>
    <h1 class="display-5 fw-bold mb-3">Typography, spacing, and color tokens should all feel coherent here.</h1>
    <p class="pb-prose mb-4">Use this preview to evaluate font pairing, body readability, button radius, container width, and how the accent color behaves against both bright and muted surfaces.</p>
    <div class="d-flex flex-wrap gap-3">
        <a href="#" class="btn pb-btn-accent">Primary Button</a>
        <a href="#" class="pb-social-chip">Secondary Surface</a>
    </div>
</section>
<section class="row g-4">
    <div class="col-lg-4">
        <div class="pb-grid-card h-100">
            <h2 class="h4 mb-3">Heading Sample</h2>
            <p class="pb-prose mb-0">This card helps judge heading rhythm against body copy and background/card contrast.</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="pb-grid-card h-100">
            <h2 class="h4 mb-3">Body Sample</h2>
            <p class="pb-prose mb-0">Container width and section spacing become easier to judge when cards sit in a balanced row like this.</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="pb-grid-card h-100">
            <h2 class="h4 mb-3">Accent Sample</h2>
            <p class="pb-prose mb-0">Accent should guide attention without overwhelming headings, body text, or footer surfaces.</p>
        </div>
    </div>
</section>
HTML,
        };

        $coreLayout = new PageBuilderCoreLayout([
            'key' => 'preview-core-layout',
            'name' => 'Preview Core Layout',
            'is_active' => true,
            'settings' => PageBuilderCoreLayout::mergeSettings([
                'font_family' => trim((string) $request->input('font_family', PageBuilderCoreLayout::defaultSettings()['font_family'])),
                'heading_font_family' => trim((string) $request->input('heading_font_family', PageBuilderCoreLayout::defaultSettings()['heading_font_family'])),
                'background_color' => trim((string) $request->input('background_color', PageBuilderCoreLayout::defaultSettings()['background_color'])),
                'card_color' => trim((string) $request->input('card_color', PageBuilderCoreLayout::defaultSettings()['card_color'])),
                'accent_color' => trim((string) $request->input('accent_color', PageBuilderCoreLayout::defaultSettings()['accent_color'])),
                'text_color' => trim((string) $request->input('text_color', PageBuilderCoreLayout::defaultSettings()['text_color'])),
                'muted_text_color' => trim((string) $request->input('muted_text_color', PageBuilderCoreLayout::defaultSettings()['muted_text_color'])),
                'button_radius' => trim((string) $request->input('button_radius', PageBuilderCoreLayout::defaultSettings()['button_radius'])),
                'container_width' => trim((string) $request->input('container_width', PageBuilderCoreLayout::defaultSettings()['container_width'])),
                'section_spacing' => trim((string) $request->input('section_spacing', PageBuilderCoreLayout::defaultSettings()['section_spacing'])),
            ]),
        ]);

        $layout = new PageBuilderLayout([
            'key' => 'preview-layout',
            'name' => 'Preview Layout',
            'core_layout_id' => null,
            'is_active' => true,
            'settings' => $preset
                ? PageBuilderLayout::mergeSettings([
                    ...data_get($preset, 'blueprint.chrome_layout.settings', []),
                    'preset' => [
                        'key' => data_get($preset, 'key'),
                        'name' => data_get($preset, 'name'),
                    ],
                ])
                : PageBuilderLayout::defaultSettings(),
        ]);

        $page = new PageBuilderPage([
            'title' => 'Core Layout Preview',
            'slug' => 'core-layout-preview',
            'is_published' => false,
            'blocks' => [],
            'settings' => [
                'layout_mode' => PageBuilderPage::LAYOUT_MODE_INCLUDE,
                'core_layout_id' => null,
                'chrome_layout_id' => null,
                'show_header' => true,
                'show_footer' => true,
                'content_mode' => PageBuilderPage::CONTENT_MODE_RAW_HTML,
                'raw_markup' => $previewMarkup,
                'preset' => $preset ? [
                    'key' => data_get($preset, 'key'),
                    'name' => data_get($preset, 'name'),
                ] : null,
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

    public function update(UpdatePageBuilderCoreLayoutRequest $request, PageBuilderCoreLayout $pageBuilderCoreLayout): RedirectResponse
    {
        $pageBuilderCoreLayout->update($request->coreLayoutPayload());

        flash()->success('Core layout updated successfully.');

        return redirect()->route('page-builder.core-layouts.edit', $pageBuilderCoreLayout);
    }

    public function destroy(PageBuilderCoreLayout $pageBuilderCoreLayout): RedirectResponse
    {
        $usedByChromeLayouts = PageBuilderLayout::query()
            ->where('core_layout_id', $pageBuilderCoreLayout->id)
            ->exists();

        $usedByPages = PageBuilderPage::query()
            ->where('settings->core_layout_id', $pageBuilderCoreLayout->id)
            ->exists();

        if ($pageBuilderCoreLayout->key === 'default' || $usedByChromeLayouts || $usedByPages) {
            flash()->error('Core layout cannot be deleted because it is default or still in use.');

            return redirect()->route('page-builder.core-layouts.index');
        }

        $pageBuilderCoreLayout->delete();

        flash()->success('Core layout deleted successfully.');

        return redirect()->route('page-builder.core-layouts.index');
    }
}
