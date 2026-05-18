<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Forms\Form;
use Modules\PageBuilder\Models\PageBuilderContentEntry;
use Modules\PageBuilder\Models\PageBuilderCoreLayout;
use Modules\PageBuilder\Models\PageBuilderLayout;
use Modules\PageBuilder\Models\PageBuilderPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageBuilderPublicController extends Controller
{
    public function show(PageBuilderPage $pageBuilderPage): View
    {
        abort_unless($pageBuilderPage->is_published, 404);

        if ($this->shouldRenderStandaloneRawHtml($pageBuilderPage)) {
            return view('pagebuilder::public.raw-standalone', $this->buildViewData($pageBuilderPage));
        }

        return view('pagebuilder::public.show', $this->buildViewData($pageBuilderPage));
    }

    public function preview(Request $request): View
    {
        $title = trim((string) $request->input('title', 'Preview Page'));
        $slugInput = trim((string) $request->input('slug', 'preview-page'));
        $blocks = $this->decodePreviewBlocks((string) $request->input('blocks_json', ''));
        $layoutMode = (string) $request->input('layout_mode', PageBuilderPage::LAYOUT_MODE_INCLUDE);
        $contentMode = (string) $request->input('content_mode', PageBuilderPage::CONTENT_MODE_BUILDER);
        $showHeader = $layoutMode === PageBuilderPage::LAYOUT_MODE_INCLUDE
            ? filter_var($request->input('show_header', false), FILTER_VALIDATE_BOOL)
            : false;
        $showFooter = $layoutMode === PageBuilderPage::LAYOUT_MODE_INCLUDE
            ? filter_var($request->input('show_footer', false), FILTER_VALIDATE_BOOL)
            : false;

        $page = new PageBuilderPage([
            'title' => $title !== '' ? $title : 'Preview Page',
            'slug' => $slugInput !== '' ? $slugInput : 'preview-page',
            'meta_title' => trim((string) $request->input('meta_title', '')) ?: null,
            'meta_description' => trim((string) $request->input('meta_description', '')) ?: null,
            'meta_keywords' => trim((string) $request->input('meta_keywords', '')) ?: null,
            'og_image_path' => trim((string) $request->input('og_image_path', '')) ?: null,
            'is_published' => false,
            'blocks' => $blocks,
            'settings' => [
                'layout_mode' => in_array($layoutMode, [
                    PageBuilderPage::LAYOUT_MODE_INCLUDE,
                    PageBuilderPage::LAYOUT_MODE_EXCLUDE,
                ], true) ? $layoutMode : PageBuilderPage::LAYOUT_MODE_INCLUDE,
                'core_layout_id' => $request->filled('core_layout_id') ? (int) $request->input('core_layout_id') : null,
                'chrome_layout_id' => $layoutMode === PageBuilderPage::LAYOUT_MODE_INCLUDE && $request->filled('chrome_layout_id')
                    ? (int) $request->input('chrome_layout_id')
                    : null,
                'show_header' => $showHeader,
                'show_footer' => $showFooter,
                'content_mode' => in_array($contentMode, [
                    PageBuilderPage::CONTENT_MODE_BUILDER,
                    PageBuilderPage::CONTENT_MODE_RAW_HTML,
                ], true) ? $contentMode : PageBuilderPage::CONTENT_MODE_BUILDER,
                'raw_markup' => trim((string) $request->input('raw_markup', '')) ?: null,
                'theme_overrides' => [
                    'accent_color' => trim((string) $request->input('theme_override_accent_color', '')) ?: null,
                    'button_radius' => trim((string) $request->input('theme_override_button_radius', '')) ?: null,
                    'container_width' => trim((string) $request->input('theme_override_container_width', '')) ?: null,
                    'section_spacing' => trim((string) $request->input('theme_override_section_spacing', '')) ?: null,
                ],
            ],
        ]);

        $viewData = $this->buildViewData($page) + [
            'disablePageBuilderAds' => true,
            'isPageBuilderPreview' => true,
        ];

        if ($this->shouldRenderStandaloneRawHtml($page)) {
            return view('pagebuilder::public.raw-standalone', $viewData);
        }

        return view('pagebuilder::public.show', $viewData);
    }

    protected function buildViewData(PageBuilderPage $pageBuilderPage): array
    {
        $blocks = is_array($pageBuilderPage->blocks) ? $pageBuilderPage->blocks : [];
        $contentTypeIds = collect($blocks)
            ->map(fn ($block) => data_get($block, 'data.content_type_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $dynamicEntries = PageBuilderContentEntry::query()
            ->whereIn('content_type_id', $contentTypeIds)
            ->where('is_published', true)
            ->latest()
            ->get()
            ->groupBy('content_type_id');

        $formIds = collect($blocks)
            ->map(fn ($block) => data_get($block, 'data.form_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $forms = Form::query()
            ->whereIn('id', $formIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $pageSettings = $pageBuilderPage->mergedSettings();
        $layoutMode = data_get($pageSettings, 'layout_mode', PageBuilderPage::LAYOUT_MODE_INCLUDE);
        $selectedChromeLayoutId = $layoutMode === PageBuilderPage::LAYOUT_MODE_INCLUDE
            ? data_get($pageSettings, 'chrome_layout_id')
            : null;
        $selectedCoreLayoutId = data_get($pageSettings, 'core_layout_id');

        $layout = $selectedChromeLayoutId
            ? PageBuilderLayout::query()
                ->where('is_active', true)
                ->find($selectedChromeLayoutId)
            : null;

        $layout ??= PageBuilderLayout::defaultRecord();

        $coreLayout = $selectedCoreLayoutId
            ? PageBuilderCoreLayout::query()
                ->where('is_active', true)
                ->find($selectedCoreLayoutId)
            : null;

        $coreLayout ??= $layout->coreLayout ?: PageBuilderCoreLayout::defaultRecord(PageBuilderLayout::legacyThemeSettings($layout->settings));

        return [
            'page' => $pageBuilderPage,
            'blocks' => $blocks,
            'layout' => $layout,
            'coreLayout' => $coreLayout,
            'dynamicEntries' => $dynamicEntries,
            'forms' => $forms,
        ];
    }

    protected function decodePreviewBlocks(string $blocksJson): array
    {
        $blocksJson = trim($blocksJson);

        if ($blocksJson === '') {
            return [];
        }

        $decoded = json_decode($blocksJson, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function shouldRenderStandaloneRawHtml(PageBuilderPage $page): bool
    {
        $settings = $page->mergedSettings();

        return data_get($settings, 'layout_mode') === PageBuilderPage::LAYOUT_MODE_EXCLUDE
            && data_get($settings, 'content_mode') === PageBuilderPage::CONTENT_MODE_RAW_HTML;
    }
}
