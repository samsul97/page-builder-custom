@php
    $pageSettings = method_exists($page, 'mergedSettings')
        ? $page->mergedSettings()
        : \App\Models\PageBuilder\PageBuilderPage::defaultSettings();
    $selectedLayoutMode = old('layout_mode', data_get($pageSettings, 'layout_mode'));
    $selectedContentMode = old('content_mode', data_get($pageSettings, 'content_mode'));
    $selectedCoreLayoutId = old('core_layout_id', data_get($pageSettings, 'core_layout_id') ?: ($coreLayouts->first()->id ?? null));
    $selectedChromeLayoutId = old('chrome_layout_id', data_get($pageSettings, 'chrome_layout_id') ?: ($chromeLayouts->first()->id ?? null));
    $rawMarkup = old('raw_markup', data_get($pageSettings, 'raw_markup'));
    $selectedCoreLayoutName = optional(($coreLayouts ?? collect())->firstWhere('id', (int) $selectedCoreLayoutId))->name;
    $selectedChromeLayoutName = optional(($chromeLayouts ?? collect())->firstWhere('id', (int) $selectedChromeLayoutId))->name;
    $adsBuilderSettings = $adsBuilderSettings ?? [];
    $adsMetaPixelReady = filled(data_get($adsBuilderSettings, 'meta_pixel_script'));
    $adsMetaCapiReady = filled(data_get($adsBuilderSettings, 'meta_pixel_id')) && filled(data_get($adsBuilderSettings, 'meta_conversion_api_token'));
    $adsTikTokReady = filled(data_get($adsBuilderSettings, 'tiktok_pixel_script'));
    $adsGoogleReady = filled(data_get($adsBuilderSettings, 'google_analytics_script'));
    $presetContext = $presetContext ?? null;
    $isPresetDriven = filled(data_get($presetContext, 'preset.key'));
    $themeOverrides = data_get($pageSettings, 'theme_overrides', []);
    $selectedThemeOverrideAccentColor = old('theme_override_accent_color', data_get($themeOverrides, 'accent_color'));
    $selectedThemeOverrideButtonRadius = old('theme_override_button_radius', data_get($themeOverrides, 'button_radius'));
    $selectedThemeOverrideContainerWidth = old('theme_override_container_width', data_get($themeOverrides, 'container_width'));
    $selectedThemeOverrideSectionSpacing = old('theme_override_section_spacing', data_get($themeOverrides, 'section_spacing'));
    $livePreviewUrl = route('page-builder.pages.preview');
    $coreLayoutAccentMap = ($coreLayouts ?? collect())->mapWithKeys(function ($coreLayout) {
        return [
            $coreLayout->id => data_get(\App\Models\PageBuilder\PageBuilderCoreLayout::mergeSettings($coreLayout->settings), 'accent_color', '#c46f35'),
        ];
    })->all();
    $selectedCoreLayoutAccentColor = $coreLayoutAccentMap[$selectedCoreLayoutId] ?? '#c46f35';
    $disabledBlockTypesUsed = $disabledBlockTypesUsed ?? [];

    $blocksJson = old(
        'blocks_json',
        json_encode($page->blocks ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    $pageBuilderEditorPayload = json_encode([
        'blocks' => $page->blocks ?? [],
        'oldBlocksJson' => old('blocks_json'),
        'uploadUrl' => route('page-builder.media.store'),
        'mediaIndexUrl' => route('page-builder.media.index'),
        'reusableBlocks' => ($reusableBlocks ?? collect())->map(function ($reusableBlock) {
            return [
                'id' => $reusableBlock->id,
                'name' => $reusableBlock->name,
                'slug' => $reusableBlock->slug,
                'description' => $reusableBlock->description,
                'blocks' => $reusableBlock->blocks ?? [],
            ];
        })->values()->all(),
        'contentTypes' => ($contentTypes ?? collect())->map(function ($contentType) {
            return [
                'id' => $contentType->id,
                'name' => $contentType->name,
                'slug' => $contentType->slug,
                'description' => $contentType->description,
                'entries_count' => $contentType->entries_count ?? 0,
            ];
        })->values()->all(),
        'forms' => ($forms ?? collect())->map(function ($form) {
            return [
                'id' => $form->id,
                'name' => $form->name,
            ];
        })->values()->all(),
        'blockTypes' => ($blockTypes ?? collect())->map(function ($blockType) {
            return [
                'type' => data_get($blockType, 'type'),
                'label' => data_get($blockType, 'label'),
                'description' => data_get($blockType, 'description'),
                'category' => data_get($blockType, 'category'),
                'status' => data_get($blockType, 'status'),
            ];
        })->values()->all(),
        'disabledBlockTypesUsed' => $disabledBlockTypesUsed,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        @if($isPresetDriven)
            <div class="card mb-4 border-primary-subtle">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Template Context</h5>
                        <p class="text-muted mb-0 small">This page belongs to a preset-driven flow. Safe page editing stays here. Deep visual system changes still live in layout records.</p>
                    </div>
                    @if(data_get($presetContext, 'preset_url'))
                        <a href="{{ data_get($presetContext, 'preset_url') }}" class="btn btn-sm btn-light">Open Preset Blueprint</a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Preset Family</div>
                                <div class="fw-semibold">{{ data_get($presetContext, 'preset.name') }}</div>
                                <div class="small text-muted mt-1">{{ data_get($presetContext, 'preset.family_name', 'Preset Family') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Starter Recipe</div>
                                <div class="fw-semibold">{{ data_get($presetContext, 'starter_page.name', '-') }}</div>
                                @if(data_get($presetContext, 'starter_page.key'))
                                    <div class="small text-muted mt-1"><code>{{ data_get($presetContext, 'starter_page.key') }}</code></div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-2">Recommended Safe Controls</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(data_get($presetContext, 'future_controls', []) as $control)
                                        <span class="badge bg-light text-dark">{{ str_replace('-', ' ', $control) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-2">Starter Purpose</div>
                                <div class="small text-muted">{{ data_get($presetContext, 'recipe.purpose', 'No starter purpose captured yet.') }}</div>
                                @if(!empty(data_get($presetContext, 'recipe.block_recipe', [])))
                                    <div class="small mt-2">
                                        <span class="text-muted">Suggested sections:</span>
                                        <code>{{ implode(', ', data_get($presetContext, 'recipe.block_recipe', [])) }}</code>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-2">Active Preset Family Assets</div>
                                @if(!empty(data_get($presetContext, 'library_assets', [])))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach(data_get($presetContext, 'library_assets', []) as $asset)
                                            <span class="badge bg-light text-dark">
                                                {{ data_get($asset, 'name') }} · {{ strtoupper(data_get($asset, 'category', 'asset')) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="small text-muted">No enabled library assets were attached to this preset draft.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-4 mb-0 small text-muted">
                        Change page title, SEO, publishing state, page settings, raw HTML, or blocks here.
                        If you need to alter the shared look and feel for this preset family, update the related Core Layout or Chrome Layout instead of assuming this page is isolated from the preset baseline.
                        @if(data_get($presetContext, 'core_layout_edit_url') || data_get($presetContext, 'chrome_layout_edit_url'))
                            <span class="d-inline-flex flex-wrap gap-2 mt-2">
                                @if(data_get($presetContext, 'core_layout_edit_url'))
                                    <a href="{{ data_get($presetContext, 'core_layout_edit_url') }}" class="btn btn-sm btn-outline-secondary">Open Core Layout</a>
                                @endif
                                @if(data_get($presetContext, 'chrome_layout_edit_url'))
                                    <a href="{{ data_get($presetContext, 'chrome_layout_edit_url') }}" class="btn btn-sm btn-outline-secondary">Open Chrome Layout</a>
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($disabledBlockTypesUsed))
            <div class="alert alert-warning border-warning-subtle mb-4">
                <div class="fw-semibold mb-1">This page uses disabled block type(s)</div>
                <div class="small">
                    Existing content is preserved, but these block types are not available for new inserts:
                    @foreach($disabledBlockTypesUsed as $disabledBlockType)
                        <code>{{ $disabledBlockType }}</code>@if(!$loop->last), @endif
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Page Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('messages.title') }}</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $page->title) }}"
                        class="form-control @error('title') is-invalid @enderror"
                        required
                    >
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $page->slug) }}"
                        class="form-control @error('slug') is-invalid @enderror"
                        placeholder="landing-page-slug"
                        required
                    >
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">SEO</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input
                        type="text"
                        id="meta_title"
                        name="meta_title"
                        value="{{ old('meta_title', $page->meta_title) }}"
                        class="form-control @error('meta_title') is-invalid @enderror"
                    >
                    @error('meta_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea
                        id="meta_description"
                        name="meta_description"
                        rows="3"
                        class="form-control @error('meta_description') is-invalid @enderror"
                    >{{ old('meta_description', $page->meta_description) }}</textarea>
                    @error('meta_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="meta_keywords" class="form-label">Keywords</label>
                    <textarea
                        id="meta_keywords"
                        name="meta_keywords"
                        rows="2"
                        class="form-control @error('meta_keywords') is-invalid @enderror"
                        placeholder="glamping bogor, villa bogor, coffee plantation"
                    >{{ old('meta_keywords', $page->meta_keywords) }}</textarea>
                    <div class="form-text">Use comma-separated keywords.</div>
                    @error('meta_keywords')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label">Social Sharing Image (OG)</label>
                    <input type="hidden" id="og_image_path" name="og_image_path" value="{{ old('og_image_path', $page->og_image_path) }}">
                    <div class="d-flex gap-2">
                        <input
                            type="text"
                            id="og_image_preview_input"
                            value="{{ old('og_image_path', $page->og_image_path) }}"
                            class="form-control"
                            placeholder="Select image from media library"
                            readonly
                        >
                        <button type="button" class="btn btn-outline-primary" id="open-og-media-library">Browse</button>
                        <button type="button" class="btn btn-light" id="clear-og-media-library">Clear</button>
                    </div>
                    <div class="form-text">Choose from the shared Page Builder media library.</div>
                    @error('og_image_path')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="mt-3" id="og_image_preview_wrapper" @if(!old('og_image_path', $page->og_image_path)) style="display: none;" @endif>
                        <div class="small text-muted mb-2">Current OG image</div>
                        <img
                            src="{{ old('og_image_path', $page->og_image_path) ? uploads_url(old('og_image_path', $page->og_image_path)) : '' }}"
                            alt="Current OG image"
                            id="og_image_preview"
                            class="img-fluid rounded border"
                            style="max-height: 220px;"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Info Ads</h5>
                    <p class="text-muted mb-0 small">This page uses the shared `ads-builder` configuration, separated from the main website ads.</p>
                </div>
                <a href="{{ route('site-settings.ads-builder.edit') }}" class="btn btn-sm btn-light">Open Ads Builder</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                <strong>Meta Pixel</strong>
                                <span class="badge {{ $adsMetaPixelReady ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $adsMetaPixelReady ? 'Configured' : 'Missing' }}
                                </span>
                            </div>
                            <div class="small text-muted">Browser-side Meta Pixel script for all Page Builder landing pages.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                <strong>Meta CAPI</strong>
                                <span class="badge {{ $adsMetaCapiReady ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $adsMetaCapiReady ? 'Configured' : 'Needs Pixel ID + Token' }}
                                </span>
                            </div>
                            <div class="small text-muted">
                                Server-side PageView uses `Meta Pixel ID` and `Access Token`.
                                @if(filled(data_get($adsBuilderSettings, 'meta_conversion_api_test_event_code')))
                                    Test Event Code is currently filled.
                                @else
                                    Test Event Code is optional and currently empty.
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                <strong>TikTok Pixel</strong>
                                <span class="badge {{ $adsTikTokReady ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $adsTikTokReady ? 'Configured' : 'Missing' }}
                                </span>
                            </div>
                            <div class="small text-muted">Browser-side TikTok Pixel script for all Page Builder landing pages.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                <strong>Google Analytics</strong>
                                <span class="badge {{ $adsGoogleReady ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $adsGoogleReady ? 'Configured' : 'Missing' }}
                                </span>
                            </div>
                            <div class="small text-muted">Browser-side Google Analytics script for all Page Builder landing pages.</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border mt-4 mb-0 small text-muted">
                    This section is informational only. To change tracking for builder pages, update `Ads Builder` in Site Settings.
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Builder Settings</h5>
            </div>
            <div class="card-body">
                @if(($coreLayouts ?? collect())->isEmpty() || ($chromeLayouts ?? collect())->isEmpty())
                    <div class="alert alert-warning border mb-4">
                        <div class="fw-semibold mb-1">No theme layout has been instantiated yet.</div>
                        <div class="small mb-2">For the current template-first flow, open Presets and instantiate a theme before creating a page with shared Core and Chrome Layouts.</div>
                        <a href="{{ route('page-builder.presets.index') }}" class="btn btn-sm btn-outline-warning">Open Presets</a>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="layout_mode" class="form-label">Layout Mode</label>
                    <select
                        id="layout_mode"
                        name="layout_mode"
                        class="form-select @error('layout_mode') is-invalid @enderror"
                    >
                        <option value="include" @selected($selectedLayoutMode === 'include')>Include Chrome Layout</option>
                        <option value="exclude" @selected($selectedLayoutMode === 'exclude')>Exclude Chrome Layout</option>
                    </select>
                    <div class="form-text">
                        Include = use header/footer chrome. Exclude = render the page without chrome.
                    </div>
                    <div class="alert alert-info border-info-subtle mt-3 mb-0 small" id="standalone-raw-html-note" @if(!($selectedLayoutMode === 'exclude' && $selectedContentMode === 'raw_html')) style="display: none;" @endif>
                        <div class="fw-semibold mb-1">Standalone Raw HTML Landing Page</div>
                        <div>
                            When Layout Mode is <strong>Exclude Chrome Layout</strong> and Content Mode is <strong>Raw HTML</strong>,
                            the public page bypasses Core Layout, Chrome Layout, theme CSS, Bootstrap, Rawdee scripts, and Ads Builder.
                            Your raw HTML becomes responsible for full HTML, CSS, JavaScript, GTM/pixel scripts, and event tracking.
                        </div>
                    </div>
                    @if($isPresetDriven)
                        <div class="small text-muted mt-2">This value was initially seeded from the preset recipe, but you can still override it for this page.</div>
                    @endif
                    @error('layout_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="core-layout-selector-wrapper">
                    <label for="core_layout_id" class="form-label">Core Layout</label>
                    <select
                        id="core_layout_id"
                        name="core_layout_id"
                        class="form-select @error('core_layout_id') is-invalid @enderror"
                    >
                        @foreach(($coreLayouts ?? collect()) as $coreLayout)
                            <option value="{{ $coreLayout->id }}" @selected((string) $selectedCoreLayoutId === (string) $coreLayout->id)>{{ $coreLayout->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Core Layout controls font family, color palette, spacing, radius, and container width.
                    </div>
                    @if($isPresetDriven)
                        <div class="small text-muted mt-2">Preset pages usually share a family Core Layout. Changing this page selection affects only this page assignment, not the preset catalog itself.</div>
                    @endif
                    @if($selectedCoreLayoutName)
                        <div class="small text-muted mt-2">Selected: {{ $selectedCoreLayoutName }}</div>
                    @endif
                    @error('core_layout_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="chrome-layout-selector-wrapper">
                    <label for="chrome_layout_id" class="form-label">Chrome Layout</label>
                    <select
                        id="chrome_layout_id"
                        name="chrome_layout_id"
                        class="form-select @error('chrome_layout_id') is-invalid @enderror"
                    >
                        @foreach(($chromeLayouts ?? collect()) as $chromeLayout)
                            <option value="{{ $chromeLayout->id }}" @selected((string) $selectedChromeLayoutId === (string) $chromeLayout->id)>{{ $chromeLayout->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Chrome Layout controls header, including navigation, plus footer. It is only used when the page includes chrome.
                    </div>
                    @if($isPresetDriven)
                        <div class="small text-muted mt-2">This page started from a preset-owned Chrome Layout. Override carefully if you want this draft to diverge from the shared family baseline.</div>
                    @endif
                    @if($selectedChromeLayoutName && $selectedLayoutMode === 'include')
                        <div class="small text-muted mt-2">Selected: {{ $selectedChromeLayoutName }}</div>
                    @endif
                    @error('chrome_layout_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content_mode" class="form-label">Content Mode</label>
                    <select
                        id="content_mode"
                        name="content_mode"
                        class="form-select @error('content_mode') is-invalid @enderror"
                    >
                        <option value="builder" @selected($selectedContentMode === 'builder')>Builder Blocks</option>
                        <option value="raw_html" @selected($selectedContentMode === 'raw_html')>Raw HTML</option>
                    </select>
                    <div class="form-text">
                        Raw HTML mode renders pasted HTML directly. Blade directives are not executed from database content.
                    </div>
                    @if($isPresetDriven)
                        <div class="small text-muted mt-2">Preset flow can still switch to Raw HTML, but doing so usually means this page is diverging from the preset block recipe.</div>
                    @endif
                    @error('content_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3" id="page-layout-toggles">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="show_header"
                                name="show_header"
                                value="1"
                                {{ old('show_header', data_get($pageSettings, 'show_header', true)) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="show_header">Show Header</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="show_footer"
                                name="show_footer"
                                value="1"
                                {{ old('show_footer', data_get($pageSettings, 'show_footer', true)) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="show_footer">Show Footer</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($isPresetDriven)
            <div class="card mt-4 border-primary-subtle">
                <div class="card-header">
                    <h5 class="card-title mb-1">Limited Theme Overrides</h5>
                    <p class="text-muted mb-0 small">Optional page-only overrides for preset drafts. Leave empty to keep the shared preset family style from the Core Layout.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="theme_override_accent_color" class="form-label">Accent Color Override</label>
                            <div class="d-flex gap-2">
                                <input
                                    type="text"
                                    id="theme_override_accent_color"
                                    name="theme_override_accent_color"
                                    value="{{ $selectedThemeOverrideAccentColor }}"
                                    class="form-control @error('theme_override_accent_color') is-invalid @enderror"
                                    placeholder="#c46f35"
                                >
                                <input
                                    type="color"
                                    id="theme_override_accent_color_picker"
                                    value="{{ $selectedThemeOverrideAccentColor ?: $selectedCoreLayoutAccentColor }}"
                                    class="form-control form-control-color"
                                    data-default-color="{{ $selectedCoreLayoutAccentColor }}"
                                >
                            </div>
                            <div class="form-text">Leave empty to inherit the shared Core Layout accent. Use the picker only as a helper.</div>
                            @error('theme_override_accent_color')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="theme_override_button_radius" class="form-label">Button Radius Override</label>
                            <input
                                type="text"
                                id="theme_override_button_radius"
                                name="theme_override_button_radius"
                                value="{{ $selectedThemeOverrideButtonRadius }}"
                                class="form-control @error('theme_override_button_radius') is-invalid @enderror"
                                placeholder="999px"
                            >
                            <div class="form-text">Examples: <code>999px</code>, <code>16px</code>, <code>1rem</code>.</div>
                            @error('theme_override_button_radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="theme_override_container_width" class="form-label">Container Width Override</label>
                            <input
                                type="text"
                                id="theme_override_container_width"
                                name="theme_override_container_width"
                                value="{{ $selectedThemeOverrideContainerWidth }}"
                                class="form-control @error('theme_override_container_width') is-invalid @enderror"
                                placeholder="1200px"
                            >
                            <div class="form-text">Examples: <code>1080px</code>, <code>1280px</code>, <code>90rem</code>.</div>
                            @error('theme_override_container_width')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="theme_override_section_spacing" class="form-label">Section Spacing Override</label>
                            <input
                                type="text"
                                id="theme_override_section_spacing"
                                name="theme_override_section_spacing"
                                value="{{ $selectedThemeOverrideSectionSpacing }}"
                                class="form-control @error('theme_override_section_spacing') is-invalid @enderror"
                                placeholder="5rem"
                            >
                            <div class="form-text">Examples: <code>4rem</code>, <code>80px</code>, <code>6rem</code>.</div>
                            @error('theme_override_section_spacing')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                        <div class="small text-muted">
                            These overrides affect only this page draft. They do not modify the preset family or the shared Core Layout used by other pages.
                        </div>
                        <button type="button" class="btn btn-sm btn-light" id="reset-theme-overrides">Reset To Preset Defaults</button>
                    </div>
                </div>
            </div>
        @endif

        @if($isPresetDriven)
            <div class="card mt-4 border-warning-subtle">
                <div class="card-header">
                    <h5 class="card-title mb-1">Advanced Editing Boundary</h5>
                    <p class="text-muted mb-0 small">The sections below are still available, but they are the fastest way to make this preset page diverge from its starter recipe.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-semibold mb-1">Raw HTML</div>
                                <div class="small text-muted">Use only if this page really needs free-form markup outside the preset block recipe.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-semibold mb-1">Visual Builder / Blocks</div>
                                <div class="small text-muted">Safe for page composition, but heavy structural edits can move the draft away from the preset baseline.</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-4 mb-0 small text-muted">
                        Recommended order for preset pages:
                        edit page details, SEO, publishing, builder settings, and ads awareness first.
                        Only then move into raw markup or deeper block restructuring if the preset flow is no longer enough.
                    </div>
                </div>
            </div>
        @endif

        <div class="card" id="page-builder-raw-markup-card" @if($selectedContentMode !== 'raw_html') style="display: none;" @endif>
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Raw HTML</h5>
                    <p class="text-muted mb-0 small">Paste direct HTML markup for this page. Use this for AI-generated landing sections or custom snippets.</p>
                </div>
                <span class="badge bg-dark-subtle text-dark">Raw Mode</span>
            </div>
            <div class="card-body">
                <div class="alert alert-warning border-warning-subtle small">
                    <div class="fw-semibold mb-1">Tracking and scripts in Raw HTML</div>
                    <div>
                        You may paste GTM, Meta Pixel, TikTok Pixel, custom JavaScript, CSS, and event triggers here.
                        For a fully standalone landing page, paste a complete document with <code>&lt;!doctype html&gt;</code>,
                        <code>&lt;html&gt;</code>, <code>&lt;head&gt;</code>, and <code>&lt;body&gt;</code>. Preview can execute pasted scripts,
                        so use GTM preview/test mode when testing tracking.
                    </div>
                </div>
                <textarea
                    id="raw_markup"
                    name="raw_markup"
                    rows="18"
                    class="form-control font-monospace @error('raw_markup') is-invalid @enderror"
                    spellcheck="false"
                    placeholder="<section>...</section>"
                >{{ $rawMarkup }}</textarea>
                @error('raw_markup')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div id="page-builder-blocks-panel" @if($selectedContentMode !== 'builder') style="display: none;" @endif>
        @if(!empty($useVisualBuilder))
            <input type="hidden" id="blocks_json" name="blocks_json" value="{{ $blocksJson }}">

            <div
                id="page-builder-editor"
                data-page-builder-editor='{{ $pageBuilderEditorPayload }}'
            ></div>

            @error('blocks_json')
                <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
            @enderror
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Blocks JSON</h5>
                        <p class="text-muted mb-0 small">Temporary editor until the visual React builder is ported here.</p>
                    </div>
                    <span class="badge bg-warning-subtle text-warning">Phase 2</span>
                </div>
                <div class="card-body">
                    <textarea
                        id="blocks_json"
                        name="blocks_json"
                        rows="18"
                        class="form-control font-monospace @error('blocks_json') is-invalid @enderror"
                        spellcheck="false"
                    >{{ $blocksJson }}</textarea>
                    @error('blocks_json')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        @endif
        </div>
    </div>

    <div class="col-lg-4">
        @if($isPresetDriven)
            <div class="card border-primary-subtle">
                <div class="card-header">
                    <h5 class="card-title mb-1">Safe Controls</h5>
                    <p class="text-muted mb-0 small">These are the recommended first edits for preset-driven pages.</p>
                </div>
                <div class="card-body">
                    <div class="vstack gap-3 small">
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">1. Page Details</div>
                            <div class="text-muted">Title and slug for the specific landing page.</div>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">2. SEO</div>
                            <div class="text-muted">Meta title, description, keywords, and OG image.</div>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">3. Publishing</div>
                            <div class="text-muted">Draft vs published state and public open flow.</div>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">4. Builder Settings</div>
                            <div class="text-muted">Per-page layout mode, content mode, and optional page-level layout assignment overrides.</div>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">5. Content</div>
                            <div class="text-muted">Blocks or raw markup only after the preset recipe and safe controls are no longer enough.</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Live Preview</h5>
                        <p class="text-muted mb-0 small">Full-page preview for layout mode, chrome selection, raw HTML, blocks, and page-level theme overrides.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light" id="refresh-live-preview">Refresh</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="expand-live-preview">Expand</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Preview viewport">
                            <button type="button" class="btn btn-outline-secondary active" data-preview-viewport="desktop">Desktop</button>
                            <button type="button" class="btn btn-outline-secondary" data-preview-viewport="tablet">Tablet</button>
                            <button type="button" class="btn btn-outline-secondary" data-preview-viewport="mobile">Mobile</button>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="auto-refresh-live-preview" checked>
                            <label class="form-check-label small" for="auto-refresh-live-preview">Auto Refresh</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-3 small mb-3">
                        <span class="text-muted" id="live-preview-status">Loading preview...</span>
                        <span class="badge bg-light text-dark">Ads disabled in preview</span>
                    </div>
                    <div id="page-builder-live-preview-shell" class="mx-auto" style="width: 100%; max-width: 100%;">
                        <div class="ratio ratio-16x9 rounded overflow-hidden border bg-light">
                            <iframe
                                id="page-builder-live-preview-frame"
                                title="Page Builder Live Preview"
                                class="w-100 h-100 border-0 bg-white"
                            ></iframe>
                        </div>
                    </div>
                    <div class="small text-muted mt-3" id="live-preview-viewport-label">
                        Desktop preview width is active.
                    </div>
                    <div class="small text-muted mt-3">
                        Preview updates automatically after page-level edits. For heavier block changes, you can pause auto refresh and use manual refresh instead.
                    </div>
                    <div class="border rounded-3 p-3 mt-3 small">
                        <div class="fw-semibold mb-2">Preview Diagnostics</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="text-muted">Layout</div>
                                <div class="fw-semibold" id="preview-diagnostic-layout">-</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Content</div>
                                <div class="fw-semibold" id="preview-diagnostic-content">-</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Blocks</div>
                                <div class="fw-semibold" id="preview-diagnostic-blocks">0</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Last Update</div>
                                <div class="fw-semibold" id="preview-diagnostic-last-update">-</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted">Payload</div>
                                <div class="fw-semibold" id="preview-diagnostic-payload">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Publishing</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="is_published"
                        name="is_published"
                        value="1"
                        {{ old('is_published', $page->is_published) ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="is_published">
                        {{ old('is_published', $page->is_published) ? __('messages.published') : __('messages.unpublished') }}
                    </label>
                </div>

                <div class="text-muted small mb-4">
                    Each page can now choose whether it includes the Page Builder layout chrome or renders standalone.
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">
                        {{ $submitLabel }}
                    </button>

                    <a href="{{ route('page-builder.pages.index') }}" class="btn btn-light">
                        {{ __('messages.cancel') }}
                    </a>
                </div>
            </div>
        </div>

        @if($page->exists)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Record Info</h5>
                </div>
                <div class="card-body small text-muted">
                    <div class="mb-2"><strong>ID:</strong> {{ $page->id }}</div>
                    <div class="mb-2"><strong>Created:</strong> {{ optional($page->created_at)->format('d M Y H:i') ?: '-' }}</div>
                    <div><strong>Updated:</strong> {{ optional($page->updated_at)->format('d M Y H:i') ?: '-' }}</div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="page-builder-media-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Media Library</h5>
                    <div class="small text-muted">Select an existing image or upload a new one.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-lg-6">
                        <label for="page-builder-media-search" class="form-label">Search Media</label>
                        <input type="text" id="page-builder-media-search" class="form-control" placeholder="Search by name or path">
                    </div>
                    <div class="col-lg-6">
                        <label for="page-builder-media-upload" class="form-label">Upload Image</label>
                        <input type="file" id="page-builder-media-upload" class="form-control" accept="image/*">
                    </div>
                </div>
                <div id="page-builder-media-error" class="alert alert-danger d-none"></div>
                <div id="page-builder-media-grid" class="row g-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="page-builder-live-preview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Expanded Live Preview</h5>
                    <div class="small text-muted">Use this view for desktop navigation, dropdown, and mega menu inspection.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <iframe id="page-builder-live-preview-modal-frame" class="w-100 h-100 border rounded bg-white" title="Expanded Page Builder Live Preview"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const layoutMode = document.getElementById('layout_mode');
        const contentMode = document.getElementById('content_mode');
        const layoutToggles = document.getElementById('page-layout-toggles');
        const blocksPanel = document.getElementById('page-builder-blocks-panel');
        const rawMarkupCard = document.getElementById('page-builder-raw-markup-card');
        const showHeader = document.getElementById('show_header');
        const showFooter = document.getElementById('show_footer');
        const standaloneRawHtmlNote = document.getElementById('standalone-raw-html-note');
        const coreLayoutSelectorWrapper = document.getElementById('core-layout-selector-wrapper');
        const coreLayoutSelector = document.getElementById('core_layout_id');
        const chromeLayoutSelectorWrapper = document.getElementById('chrome-layout-selector-wrapper');
        const chromeLayoutSelector = document.getElementById('chrome_layout_id');
        const themeOverrideAccentInput = document.getElementById('theme_override_accent_color');
        const themeOverrideAccentPicker = document.getElementById('theme_override_accent_color_picker');
        const themeOverrideButtonRadiusInput = document.getElementById('theme_override_button_radius');
        const themeOverrideContainerWidthInput = document.getElementById('theme_override_container_width');
        const themeOverrideSectionSpacingInput = document.getElementById('theme_override_section_spacing');
        const resetThemeOverridesButton = document.getElementById('reset-theme-overrides');
        const ogImagePathInput = document.getElementById('og_image_path');
        const ogImagePreviewInput = document.getElementById('og_image_preview_input');
        const ogImagePreviewWrapper = document.getElementById('og_image_preview_wrapper');
        const ogImagePreview = document.getElementById('og_image_preview');
        const openOgMediaButton = document.getElementById('open-og-media-library');
        const clearOgMediaButton = document.getElementById('clear-og-media-library');
        const mediaModalElement = document.getElementById('page-builder-media-modal');
        const mediaSearchInput = document.getElementById('page-builder-media-search');
        const mediaUploadInput = document.getElementById('page-builder-media-upload');
        const mediaGrid = document.getElementById('page-builder-media-grid');
        const mediaError = document.getElementById('page-builder-media-error');
        const mediaModal = mediaModalElement ? new bootstrap.Modal(mediaModalElement) : null;
        const mediaIndexUrl = @js(route('page-builder.media.index'));
        const mediaUploadUrl = @js(route('page-builder.media.store'));
        const livePreviewUrl = @js($livePreviewUrl);
        const livePreviewFrame = document.getElementById('page-builder-live-preview-frame');
        const livePreviewShell = document.getElementById('page-builder-live-preview-shell');
        const livePreviewStatus = document.getElementById('live-preview-status');
        const livePreviewViewportLabel = document.getElementById('live-preview-viewport-label');
        const refreshLivePreviewButton = document.getElementById('refresh-live-preview');
        const expandLivePreviewButton = document.getElementById('expand-live-preview');
        const livePreviewModalElement = document.getElementById('page-builder-live-preview-modal');
        const livePreviewModalFrame = document.getElementById('page-builder-live-preview-modal-frame');
        const livePreviewModal = livePreviewModalElement ? new bootstrap.Modal(livePreviewModalElement) : null;
        const autoRefreshLivePreview = document.getElementById('auto-refresh-live-preview');
        const previewViewportButtons = Array.from(document.querySelectorAll('[data-preview-viewport]'));
        const previewDiagnosticLayout = document.getElementById('preview-diagnostic-layout');
        const previewDiagnosticContent = document.getElementById('preview-diagnostic-content');
        const previewDiagnosticBlocks = document.getElementById('preview-diagnostic-blocks');
        const previewDiagnosticLastUpdate = document.getElementById('preview-diagnostic-last-update');
        const previewDiagnosticPayload = document.getElementById('preview-diagnostic-payload');
        const pageForm = livePreviewFrame ? livePreviewFrame.closest('form') : null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const chromeLayouts = @js(($chromeLayouts ?? collect())->map(fn ($layout) => [
            'id' => $layout->id,
            'name' => $layout->name,
            'core_layout_id' => $layout->core_layout_id,
        ])->values()->all());
        const coreLayoutAccentMap = @js($coreLayoutAccentMap);
        let livePreviewRequestId = 0;
        let livePreviewDebounce = null;
        let lastBlocksJsonValue = document.getElementById('blocks_json')?.value || '';
        let isAutoRefreshEnabled = autoRefreshLivePreview ? autoRefreshLivePreview.checked : true;
        let livePreviewAbortController = null;

        const previewViewportMap = {
            desktop: {
                width: '100%',
                label: 'Desktop preview width is active.',
            },
            tablet: {
                width: '820px',
                label: 'Tablet preview width is active.',
            },
            mobile: {
                width: '430px',
                label: 'Mobile preview width is active.',
            },
        };

        const allChromeOptions = chromeLayoutSelector
            ? Array.from(chromeLayoutSelector.options).map((option) => ({
                value: option.value,
                label: option.textContent,
            }))
            : [];

        const syncChromeLayoutOptions = () => {
            if (!chromeLayoutSelector || !coreLayoutSelector) {
                return;
            }

            const selectedCoreLayoutId = coreLayoutSelector.value;
            const selectedChromeLayoutId = chromeLayoutSelector.value;
            const matchingIds = new Set(
                chromeLayouts
                    .filter((layout) => String(layout.core_layout_id) === String(selectedCoreLayoutId))
                    .map((layout) => String(layout.id))
            );

            chromeLayoutSelector.innerHTML = '';

            allChromeOptions
                .filter((option) => matchingIds.has(String(option.value)))
                .forEach((option) => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.label;
                    chromeLayoutSelector.appendChild(optionElement);
                });

            if (!matchingIds.has(String(selectedChromeLayoutId))) {
                const firstOptionValue = chromeLayoutSelector.options[0]?.value ?? '';
                chromeLayoutSelector.value = firstOptionValue;
            } else {
                chromeLayoutSelector.value = selectedChromeLayoutId;
            }
        };

        const syncThemeOverrideAccentPicker = () => {
            if (!themeOverrideAccentPicker || !themeOverrideAccentInput) {
                return;
            }

            const fallbackColor = coreLayoutAccentMap[String(coreLayoutSelector?.value)] || themeOverrideAccentPicker.dataset.defaultColor || '#c46f35';
            themeOverrideAccentPicker.value = themeOverrideAccentInput.value || fallbackColor;
        };

        const setLivePreviewStatus = (message, tone = 'muted') => {
            if (!livePreviewStatus) {
                return;
            }

            livePreviewStatus.className = '';

            if (tone === 'danger') {
                livePreviewStatus.classList.add('text-danger');
            } else if (tone === 'success') {
                livePreviewStatus.classList.add('text-success');
            } else {
                livePreviewStatus.classList.add('text-muted');
            }

            livePreviewStatus.textContent = message;
        };

        const formatBytes = (bytes) => {
            if (!Number.isFinite(bytes) || bytes <= 0) {
                return '0 B';
            }

            if (bytes < 1024) {
                return `${bytes} B`;
            }

            if (bytes < 1024 * 1024) {
                return `${(bytes / 1024).toFixed(1)} KB`;
            }

            return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
        };

        const countPreviewBlocks = () => {
            const blocksJson = document.getElementById('blocks_json')?.value || '';

            if (!blocksJson.trim()) {
                return 0;
            }

            try {
                const decoded = JSON.parse(blocksJson);
                return Array.isArray(decoded) ? decoded.length : 0;
            } catch (error) {
                return 'Invalid JSON';
            }
        };

        const updatePreviewDiagnostics = (formData = null, duration = null) => {
            if (previewDiagnosticLayout) {
                previewDiagnosticLayout.textContent = layoutMode?.value || '-';
            }

            if (previewDiagnosticContent) {
                previewDiagnosticContent.textContent = contentMode?.value || '-';
            }

            if (previewDiagnosticBlocks) {
                previewDiagnosticBlocks.textContent = String(countPreviewBlocks());
            }

            if (previewDiagnosticLastUpdate && duration !== null) {
                previewDiagnosticLastUpdate.textContent = `${Math.round(duration)} ms`;
            }

            if (previewDiagnosticPayload && formData) {
                const payloadBytes = Array.from(formData.entries()).reduce((total, [key, value]) => {
                    const valueSize = value instanceof File ? value.size : String(value).length;
                    return total + String(key).length + valueSize;
                }, 0);

                previewDiagnosticPayload.textContent = formatBytes(payloadBytes);
            }
        };

        const refreshLivePreview = async () => {
            if (!pageForm || !livePreviewFrame || !livePreviewUrl) {
                return;
            }

            const requestId = ++livePreviewRequestId;
            const formData = new FormData(pageForm);
            formData.delete('_method');
            const startedAt = performance.now();

            if (livePreviewAbortController) {
                livePreviewAbortController.abort();
            }

            livePreviewAbortController = new AbortController();

            setLivePreviewStatus('Updating preview...', 'muted');
            updatePreviewDiagnostics(formData);

            try {
                const response = await fetch(livePreviewUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                    credentials: 'same-origin',
                    signal: livePreviewAbortController.signal,
                });

                if (!response.ok) {
                    throw new Error('Preview request failed.');
                }

                const html = await response.text();

                if (requestId !== livePreviewRequestId) {
                    return;
                }

                livePreviewFrame.srcdoc = html;
                if (livePreviewModalFrame) {
                    livePreviewModalFrame.srcdoc = html;
                }
                const duration = performance.now() - startedAt;
                setLivePreviewStatus(`Preview updated in ${Math.round(duration)} ms.`, 'success');
                updatePreviewDiagnostics(formData, duration);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                setLivePreviewStatus(error.message || 'Preview failed to load.', 'danger');
            }
        };

        const queueLivePreviewRefresh = (delay = 450, statusMessage = 'Updating preview...') => {
            if (!livePreviewFrame) {
                return;
            }

            if (!isAutoRefreshEnabled) {
                setLivePreviewStatus('Preview paused. Use Refresh to reload.', 'muted');
                return;
            }

            window.clearTimeout(livePreviewDebounce);
            setLivePreviewStatus(statusMessage, 'muted');
            livePreviewDebounce = window.setTimeout(refreshLivePreview, delay);
        };

        const setPreviewViewport = (viewport) => {
            const config = previewViewportMap[viewport] || previewViewportMap.desktop;

            if (livePreviewShell) {
                livePreviewShell.style.maxWidth = config.width;
            }

            if (livePreviewViewportLabel) {
                livePreviewViewportLabel.textContent = config.label;
            }

            previewViewportButtons.forEach((button) => {
                button.classList.toggle('active', button.dataset.previewViewport === viewport);
            });
        };

        const syncLayoutMode = () => {
            const isExclude = layoutMode?.value === 'exclude';
            const isStandaloneRawHtml = isExclude && contentMode?.value === 'raw_html';

            if (layoutToggles) {
                layoutToggles.style.display = isExclude ? 'none' : '';
            }

            if (coreLayoutSelectorWrapper) {
                coreLayoutSelectorWrapper.style.display = isStandaloneRawHtml ? 'none' : '';
            }

            if (chromeLayoutSelectorWrapper) {
                chromeLayoutSelectorWrapper.style.display = isExclude ? 'none' : '';
            }

            if (standaloneRawHtmlNote) {
                standaloneRawHtmlNote.style.display = isStandaloneRawHtml ? '' : 'none';
            }

            if (isExclude) {
                if (showHeader) {
                    showHeader.checked = false;
                }

                if (showFooter) {
                    showFooter.checked = false;
                }
            }
        };

        const syncContentMode = () => {
            const isRawMode = contentMode?.value === 'raw_html';

            if (blocksPanel) {
                blocksPanel.style.display = isRawMode ? 'none' : '';
            }

            if (rawMarkupCard) {
                rawMarkupCard.style.display = isRawMode ? '' : 'none';
            }

            syncLayoutMode();
        };

        layoutMode?.addEventListener('change', () => {
            syncLayoutMode();
            queueLivePreviewRefresh();
        });
        contentMode?.addEventListener('change', () => {
            syncContentMode();
            queueLivePreviewRefresh();
        });
        coreLayoutSelector?.addEventListener('change', () => {
            syncChromeLayoutOptions();
            syncThemeOverrideAccentPicker();
            queueLivePreviewRefresh();
        });
        chromeLayoutSelector?.addEventListener('change', queueLivePreviewRefresh);
        showHeader?.addEventListener('change', queueLivePreviewRefresh);
        showFooter?.addEventListener('change', queueLivePreviewRefresh);
        themeOverrideAccentPicker?.addEventListener('input', () => {
            if (themeOverrideAccentInput) {
                themeOverrideAccentInput.value = themeOverrideAccentPicker.value;
            }

            queueLivePreviewRefresh();
        });
        themeOverrideAccentInput?.addEventListener('input', () => {
            syncThemeOverrideAccentPicker();
            queueLivePreviewRefresh();
        });
        themeOverrideButtonRadiusInput?.addEventListener('input', queueLivePreviewRefresh);
        themeOverrideContainerWidthInput?.addEventListener('input', queueLivePreviewRefresh);
        themeOverrideSectionSpacingInput?.addEventListener('input', queueLivePreviewRefresh);
        resetThemeOverridesButton?.addEventListener('click', () => {
            if (themeOverrideAccentInput) {
                themeOverrideAccentInput.value = '';
            }

            if (themeOverrideButtonRadiusInput) {
                themeOverrideButtonRadiusInput.value = '';
            }

            if (themeOverrideContainerWidthInput) {
                themeOverrideContainerWidthInput.value = '';
            }

            if (themeOverrideSectionSpacingInput) {
                themeOverrideSectionSpacingInput.value = '';
            }

            syncThemeOverrideAccentPicker();
            queueLivePreviewRefresh();
        });

        syncChromeLayoutOptions();
        syncLayoutMode();
        syncContentMode();
        syncThemeOverrideAccentPicker();
        updatePreviewDiagnostics();
        refreshLivePreviewButton?.addEventListener('click', refreshLivePreview);
        expandLivePreviewButton?.addEventListener('click', () => {
            if (livePreviewModalFrame && livePreviewFrame) {
                livePreviewModalFrame.srcdoc = livePreviewFrame.srcdoc || '';
            }

            livePreviewModal?.show();
        });
        autoRefreshLivePreview?.addEventListener('change', function () {
            isAutoRefreshEnabled = this.checked;

            if (isAutoRefreshEnabled) {
                queueLivePreviewRefresh();
            } else {
                setLivePreviewStatus('Preview paused. Use Refresh to reload.', 'muted');
            }
        });
        previewViewportButtons.forEach((button) => {
            button.addEventListener('click', function () {
                setPreviewViewport(this.dataset.previewViewport || 'desktop');
            });
        });

        const setOgPreview = (path, url) => {
            if (ogImagePathInput) {
                ogImagePathInput.value = path || '';
            }

            if (ogImagePreviewInput) {
                ogImagePreviewInput.value = path || '';
            }

            if (ogImagePreview && url) {
                ogImagePreview.src = url;
            }

            if (ogImagePreviewWrapper) {
                ogImagePreviewWrapper.style.display = path ? '' : 'none';
            }

            queueLivePreviewRefresh();
        };

        const showMediaError = (message) => {
            if (!mediaError) {
                return;
            }

            mediaError.textContent = message || '';
            mediaError.classList.toggle('d-none', !message);
        };

        const renderMediaItems = (items) => {
            if (!mediaGrid) {
                return;
            }

            if (!items.length) {
                mediaGrid.innerHTML = '<div class="col-12 text-center text-muted py-5">No media found.</div>';
                return;
            }

            mediaGrid.innerHTML = items.map((item) => `
                <div class="col-md-4 col-xl-3">
                    <button
                        type="button"
                        class="card h-100 border shadow-sm text-start w-100 bg-white"
                        data-media-path="${item.path}"
                        data-media-url="${item.url}"
                    >
                        <div class="ratio ratio-4x3 bg-light rounded-top overflow-hidden">
                            ${item.is_image ? `<img src="${item.url}" alt="${item.name}" class="w-100 h-100 object-fit-cover">` : '<div class="d-flex align-items-center justify-content-center text-muted">No preview</div>'}
                        </div>
                        <div class="card-body">
                            <div class="fw-semibold text-truncate">${item.name}</div>
                            <div class="small text-muted text-truncate">${item.path}</div>
                        </div>
                    </button>
                </div>
            `).join('');

            mediaGrid.querySelectorAll('[data-media-path]').forEach((button) => {
                button.addEventListener('click', function () {
                    const path = this.getAttribute('data-media-path');
                    const url = this.getAttribute('data-media-url');

                    setOgPreview(path, url);

                    if (mediaModal) {
                        mediaModal.hide();
                    }
                });
            });
        };

        const loadMediaItems = async (query = '') => {
            showMediaError('');

            if (mediaGrid) {
                mediaGrid.innerHTML = '<div class="col-12 text-center text-muted py-5">Loading media...</div>';
            }

            try {
                const url = new URL(mediaIndexUrl, window.location.origin);
                url.searchParams.set('json', '1');

                if (query) {
                    url.searchParams.set('q', query);
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                });

                const result = await response.json();
                renderMediaItems(Array.isArray(result.media) ? result.media : []);
            } catch (error) {
                showMediaError(error.message || 'Failed to load media library.');
            }
        };

        openOgMediaButton?.addEventListener('click', function () {
            if (mediaModal) {
                mediaModal.show();
                loadMediaItems(mediaSearchInput?.value || '');
            }
        });

        clearOgMediaButton?.addEventListener('click', function () {
            setOgPreview('', '');
        });

        mediaSearchInput?.addEventListener('input', function () {
            loadMediaItems(this.value);
        });

        mediaUploadInput?.addEventListener('change', async function () {
            const file = this.files?.[0];

            if (!file) {
                return;
            }

            showMediaError('');

            try {
                const formData = new FormData();
                formData.append('file', file);

                const response = await fetch(mediaUploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        Accept: 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin',
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Upload failed');
                }

                loadMediaItems(mediaSearchInput?.value || '');
            } catch (error) {
                showMediaError(error.message || 'Upload failed.');
            } finally {
                this.value = '';
            }
        });

        [
            'title',
            'slug',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ].forEach((fieldId) => {
            document.getElementById(fieldId)?.addEventListener('input', queueLivePreviewRefresh);
        });

        document.getElementById('raw_markup')?.addEventListener('input', () => {
            queueLivePreviewRefresh(650, 'Refreshing markup preview...');
        });

        window.setInterval(() => {
            const blocksJsonInput = document.getElementById('blocks_json');
            const nextBlocksJsonValue = blocksJsonInput?.value || '';

            if (nextBlocksJsonValue !== lastBlocksJsonValue) {
                lastBlocksJsonValue = nextBlocksJsonValue;
                queueLivePreviewRefresh(900, 'Block changes detected. Refreshing preview...');
            }
        }, 700);

        setPreviewViewport('desktop');
        queueLivePreviewRefresh();
    });
</script>
