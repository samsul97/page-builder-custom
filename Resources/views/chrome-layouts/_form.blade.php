@php
    $settings = \App\Models\PageBuilder\PageBuilderLayout::mergeSettings($layout->settings);
    $presetKey = $presetKey ?? data_get($layout->settings, 'preset.key');
    $presetName = data_get($layout->settings, 'preset.name');
    $isPresetOwned = filled($presetKey);
    $navigation = data_get($settings, 'navigation', []);
    $chromePreviewUrl = route('page-builder.chrome-layouts.preview');
    $headerBrandLogoPath = old('header_brand_logo_url', data_get($settings, 'header.brand_logo_url'));
    $headerBrandLogoPreview = filled($headerBrandLogoPath) && !str_starts_with($headerBrandLogoPath, 'http://') && !str_starts_with($headerBrandLogoPath, 'https://')
        ? uploads_url($headerBrandLogoPath)
        : $headerBrandLogoPath;
    $headerLinks = old('header_links', data_get($settings, 'header.links', []));
    $footerSocialLinks = old('footer_social_links', data_get($settings, 'footer.social_links', []));
    $footerJourneyLinks = old('footer_journey_links', data_get($settings, 'footer.journey_links', []));
    $footerContacts = old('footer_contacts', data_get($settings, 'footer.contacts', []));
    $footerLocations = old('footer_locations', data_get($settings, 'footer.locations', []));

    while (count($headerLinks) < 5) {
        $headerLinks[] = ['type' => 'link', 'label' => '', 'url' => '', 'children' => [], 'sections' => []];
    }

    while (count($footerSocialLinks) < 3) {
        $footerSocialLinks[] = ['label' => '', 'url' => ''];
    }

    while (count($footerJourneyLinks) < 4) {
        $footerJourneyLinks[] = ['label' => '', 'url' => ''];
    }

    while (count($footerContacts) < 4) {
        $footerContacts[] = ['label' => '', 'phone' => ''];
    }

    while (count($footerLocations) < 2) {
        $footerLocations[] = ['label' => '', 'map_url' => '', 'lines' => ['', '', ''], 'weekday_label' => '', 'weekday_value' => '', 'weekend_label' => '', 'weekend_value' => ''];
    }

    foreach ($footerLocations as $index => $location) {
        $footerLocations[$index]['lines'] = array_pad(data_get($location, 'lines', []), 3, '');
    }
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Chrome Layout Details</h5>
            </div>
            <div class="card-body">
                @if($isPresetOwned)
                    <div class="alert alert-light border mb-4">
                        <div class="fw-semibold mb-1">Preset-Owned Chrome Layout</div>
                        <div class="small text-muted mb-2">Content values can still be edited here, but the preset record identity should stay stable. Treat this as safe content editing, not unlimited structural redesign.</div>
                        <a href="{{ route('page-builder.presets.show', $presetKey) }}" class="btn btn-sm btn-outline-secondary">Open Preset</a>
                    </div>
                @endif
                @if(($coreLayouts ?? collect())->isEmpty())
                    <div class="alert alert-warning border mb-4">
                        <div class="fw-semibold mb-1">No user-facing Core Layout exists yet.</div>
                        <div class="small mb-2">Instantiate a preset theme first, or create a Core Layout manually before creating Chrome Layouts.</div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('page-builder.presets.index') }}" class="btn btn-sm btn-outline-warning">Open Presets</a>
                            <a href="{{ route('page-builder.core-layouts.create') }}" class="btn btn-sm btn-light">Create Core Layout</a>
                        </div>
                    </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', $layout->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Key</label>
                        @if($isPresetOwned)
                            <input type="hidden" name="key" value="{{ old('key', $layout->key) }}">
                        @endif
                        <input type="text" name="key" value="{{ old('key', $layout->key) }}" class="form-control @error('key') is-invalid @enderror" required {{ $isPresetOwned ? 'disabled' : '' }}>
                        <div class="form-text">
                            @if($isPresetOwned)
                                Key is locked because this record is owned by preset <code>{{ $presetKey }}</code>.
                            @else
                                Key is auto-generated from the name, but you can still refine it if needed.
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Core Layout</label>
                        <select name="core_layout_id" class="form-select @error('core_layout_id') is-invalid @enderror" required>
                            @foreach($coreLayouts as $coreLayout)
                                <option value="{{ $coreLayout->id }}" @selected((string) old('core_layout_id', $layout->core_layout_id) === (string) $coreLayout->id)>{{ $coreLayout->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($isPresetOwned)
                        <div class="col-md-6">
                            <label class="form-label">Preset Family</label>
                            <input type="text" value="{{ $presetName ?: $presetKey }}" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ownership</label>
                            <input type="text" value="Preset Theme" class="form-control" disabled>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label">Header Variant</label>
                        <select name="header_variant" class="form-select @error('header_variant') is-invalid @enderror" required>
                            <option value="classic" @selected(old('header_variant', data_get($settings, 'header.variant', 'classic')) === 'classic')>Classic Left Brand</option>
                            <option value="centered" @selected(old('header_variant', data_get($settings, 'header.variant', 'classic')) === 'centered')>Centered Brand Stack</option>
                        </select>
                        <div class="form-text">Controls the overall header structure without changing the data fields.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Footer Variant</label>
                        <select name="footer_variant" class="form-select @error('footer_variant') is-invalid @enderror" required>
                            <option value="columns" @selected(old('footer_variant', data_get($settings, 'footer.variant', 'columns')) === 'columns')>Columns Footer</option>
                            <option value="minimal" @selected(old('footer_variant', data_get($settings, 'footer.variant', 'columns')) === 'minimal')>Minimal Footer</option>
                        </select>
                        <div class="form-text">Controls whether footer renders as full columns or a simpler compact footer.</div>
                    </div>
                </div>
            </div>
        </div>

        @if($isPresetOwned)
            <div class="card border-primary-subtle">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Preset-Safe Fields</h5>
                        <p class="text-muted mb-0 small">Use the sections below for content and universal visual overrides that still fit the shared chrome schema.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">Safe</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 small">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-semibold mb-1">Safe Content</div>
                                <div class="text-muted">Brand copy, CTA text/URL, announcement text, social links, journey links, contacts, and location details.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-semibold mb-1">Safe Visual Overrides</div>
                                <div class="text-muted">Header variant, footer variant, navigation density, header surface, and footer surface.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">{{ $isPresetOwned ? 'Navigation + Visual Overrides' : 'Navigation' }}</h5>
                    <p class="text-muted mb-0 small">
                        @if($isPresetOwned)
                            Safe area for navigation behavior, top bar/meta text, and universal visual overrides.
                        @else
                            Navigation behavior and visual chrome settings.
                        @endif
                    </p>
                </div>
                @if($isPresetOwned)
                    <span class="badge bg-primary-subtle text-primary">Safe</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Navigation Style</label>
                        <select name="navigation_style" class="form-select @error('navigation_style') is-invalid @enderror" required>
                            <option value="inline" @selected(old('navigation_style', data_get($navigation, 'style', 'inline')) === 'inline')>Inline Links</option>
                            <option value="pill" @selected(old('navigation_style', data_get($navigation, 'style', 'inline')) === 'pill')>Pill Links</option>
                        </select>
                        <div class="form-text">Controls whether navbar links render as plain text or rounded pills.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Navigation Density</label>
                        <select name="navigation_density" class="form-select @error('navigation_density') is-invalid @enderror" required>
                            <option value="compact" @selected(old('navigation_density', data_get($navigation, 'density', 'comfortable')) === 'compact')>Compact</option>
                            <option value="comfortable" @selected(old('navigation_density', data_get($navigation, 'density', 'comfortable')) === 'comfortable')>Comfortable</option>
                            <option value="relaxed" @selected(old('navigation_density', data_get($navigation, 'density', 'comfortable')) === 'relaxed')>Relaxed</option>
                        </select>
                        <div class="form-text">Controls the overall spacing and breathing room of the navigation chrome.</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="navigation_is_sticky" value="1" {{ old('navigation_is_sticky', data_get($navigation, 'is_sticky', true)) ? 'checked' : '' }}>
                            <label class="form-check-label">Sticky navigation</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="navigation_show_top_bar" value="1" {{ old('navigation_show_top_bar', data_get($navigation, 'show_top_bar', false)) ? 'checked' : '' }}>
                            <label class="form-check-label">Show top bar</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Top Bar Text</label>
                        <input type="text" name="navigation_top_bar_text" value="{{ old('navigation_top_bar_text', data_get($navigation, 'top_bar_text')) }}" class="form-control @error('navigation_top_bar_text') is-invalid @enderror" placeholder="Free shipping, opening hours, promo note, or announcement">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Meta Label</label>
                        <input type="text" name="navigation_meta_label" value="{{ old('navigation_meta_label', data_get($navigation, 'meta_label')) }}" class="form-control @error('navigation_meta_label') is-invalid @enderror" placeholder="Call Center">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Meta Value</label>
                        <input type="text" name="navigation_meta_value" value="{{ old('navigation_meta_value', data_get($navigation, 'meta_value')) }}" class="form-control @error('navigation_meta_value') is-invalid @enderror" placeholder="+62 812 3456 7890">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Meta URL</label>
                        <input type="text" name="navigation_meta_url" value="{{ old('navigation_meta_url', data_get($navigation, 'meta_url')) }}" class="form-control @error('navigation_meta_url') is-invalid @enderror" placeholder="tel:+6281234567890 or https://wa.me/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Top Bar Link Label</label>
                        <input type="text" name="navigation_top_bar_link_label" value="{{ old('navigation_top_bar_link_label', data_get($navigation, 'top_bar_link_label')) }}" class="form-control @error('navigation_top_bar_link_label') is-invalid @enderror" placeholder="Read More">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Top Bar Link URL</label>
                        <input type="text" name="navigation_top_bar_link_url" value="{{ old('navigation_top_bar_link_url', data_get($navigation, 'top_bar_link_url')) }}" class="form-control @error('navigation_top_bar_link_url') is-invalid @enderror" placeholder="/promo or https://...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Header Surface</label>
                        <select name="chrome_header_surface_style" class="form-select @error('chrome_header_surface_style') is-invalid @enderror" required>
                            <option value="solid" @selected(old('chrome_header_surface_style', data_get($settings, 'chrome_visual.header_surface_style', 'glass')) === 'solid')>Solid</option>
                            <option value="glass" @selected(old('chrome_header_surface_style', data_get($settings, 'chrome_visual.header_surface_style', 'glass')) === 'glass')>Glass</option>
                            <option value="minimal" @selected(old('chrome_header_surface_style', data_get($settings, 'chrome_visual.header_surface_style', 'glass')) === 'minimal')>Minimal</option>
                        </select>
                        <div class="form-text">Changes the header shell feel without changing the header data structure.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Header Content</h5>
                    <p class="text-muted mb-0 small">Brand, CTA, and logo content for this chrome layout.</p>
                </div>
                @if($isPresetOwned)
                    <span class="badge bg-primary-subtle text-primary">Safe</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Brand Name</label>
                        <input type="text" name="header_brand_name" value="{{ old('header_brand_name', data_get($settings, 'header.brand_name')) }}" class="form-control @error('header_brand_name') is-invalid @enderror" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="header_tagline" value="{{ old('header_tagline', data_get($settings, 'header.tagline')) }}" class="form-control @error('header_tagline') is-invalid @enderror">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Brand Logo</label>
                        <input type="hidden" name="header_brand_logo_url" id="header_brand_logo_url" value="{{ $headerBrandLogoPath }}">
                        <div class="input-group">
                            <input type="text" id="header_brand_logo_preview_input" value="{{ $headerBrandLogoPath }}" class="form-control @error('header_brand_logo_url') is-invalid @enderror" placeholder="Choose from Media Library" readonly>
                            <button type="button" class="btn btn-outline-secondary" id="open-layout-logo-media-modal">Browse</button>
                            <button type="button" class="btn btn-outline-danger" id="clear-layout-logo-media">Clear</button>
                        </div>
                        @error('header_brand_logo_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="mt-3" id="header_brand_logo_preview_wrapper" @if(!filled($headerBrandLogoPath)) style="display: none;" @endif>
                            <img
                                src="{{ $headerBrandLogoPreview }}"
                                alt="Brand logo preview"
                                id="header_brand_logo_preview"
                                class="img-fluid rounded border"
                                style="max-height: 120px;"
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Brand Logo Alt</label>
                        <input type="text" name="header_brand_logo_alt" value="{{ old('header_brand_logo_alt', data_get($settings, 'header.brand_logo_alt')) }}" class="form-control @error('header_brand_logo_alt') is-invalid @enderror">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Header Button Label</label>
                        <input type="text" name="header_button_label" value="{{ old('header_button_label', data_get($settings, 'header.button_label')) }}" class="form-control @error('header_button_label') is-invalid @enderror">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Header Button URL</label>
                        <input type="text" name="header_button_url" value="{{ old('header_button_url', data_get($settings, 'header.button_url')) }}" class="form-control @error('header_button_url') is-invalid @enderror">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Footer Brand + Social</h5>
                    <p class="text-muted mb-0 small">Safe footer copy and social values.</p>
                </div>
                @if($isPresetOwned)
                    <span class="badge bg-primary-subtle text-primary">Safe</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Brand Title</label>
                        <input type="text" name="footer_brand_title" value="{{ old('footer_brand_title', data_get($settings, 'footer.brand_title')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Social Title</label>
                        <input type="text" name="footer_social_title" value="{{ old('footer_social_title', data_get($settings, 'footer.social_title')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Footer Surface</label>
                        <select name="footer_surface_style" class="form-select @error('footer_surface_style') is-invalid @enderror" required>
                            <option value="dark" @selected(old('footer_surface_style', data_get($settings, 'footer.surface_style', 'dark')) === 'dark')>Dark</option>
                            <option value="soft" @selected(old('footer_surface_style', data_get($settings, 'footer.surface_style', 'dark')) === 'soft')>Soft</option>
                            <option value="light" @selected(old('footer_surface_style', data_get($settings, 'footer.surface_style', 'dark')) === 'light')>Light</option>
                        </select>
                        <div class="form-text">Changes footer mood without changing footer content structure.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Brand Text</label>
                        <textarea name="footer_brand_text" rows="4" class="form-control">{{ old('footer_brand_text', data_get($settings, 'footer.brand_text')) }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row g-3">
                    @foreach($footerSocialLinks as $index => $link)
                        <div class="col-md-6">
                            <label class="form-label">Social Label {{ $index + 1 }}</label>
                            <input type="text" name="footer_social_links[{{ $index }}][label]" value="{{ data_get($link, 'label') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Social URL {{ $index + 1 }}</label>
                            <input type="text" name="footer_social_links[{{ $index }}][url]" value="{{ data_get($link, 'url') }}" class="form-control">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Footer Journey + Contacts</h5>
                    <p class="text-muted mb-0 small">Safe link and contact values that still fit the shared footer schema.</p>
                </div>
                @if($isPresetOwned)
                    <span class="badge bg-primary-subtle text-primary">Safe</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Journey Title</label>
                        <input type="text" name="footer_journey_title" value="{{ old('footer_journey_title', data_get($settings, 'footer.journey_title')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Title</label>
                        <input type="text" name="footer_contact_title" value="{{ old('footer_contact_title', data_get($settings, 'footer.contact_title')) }}" class="form-control">
                    </div>
                </div>

                <div class="row g-3">
                    @foreach($footerJourneyLinks as $index => $link)
                        <div class="col-md-6">
                            <label class="form-label">Journey Label {{ $index + 1 }}</label>
                            <input type="text" name="footer_journey_links[{{ $index }}][label]" value="{{ data_get($link, 'label') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Journey URL {{ $index + 1 }}</label>
                            <input type="text" name="footer_journey_links[{{ $index }}][url]" value="{{ data_get($link, 'url') }}" class="form-control">
                        </div>
                    @endforeach
                </div>

                <hr class="my-4">

                <div class="row g-3">
                    @foreach($footerContacts as $index => $contact)
                        <div class="col-md-6">
                            <label class="form-label">Contact Label {{ $index + 1 }}</label>
                            <input type="text" name="footer_contacts[{{ $index }}][label]" value="{{ data_get($contact, 'label') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone {{ $index + 1 }}</label>
                            <input type="text" name="footer_contacts[{{ $index }}][phone]" value="{{ data_get($contact, 'phone') }}" class="form-control">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Footer Locations</h5>
                    <p class="text-muted mb-0 small">Safe location and opening-hours values.</p>
                </div>
                @if($isPresetOwned)
                    <span class="badge bg-primary-subtle text-primary">Safe</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Location Title</label>
                        <input type="text" name="footer_location_title" value="{{ old('footer_location_title', data_get($settings, 'footer.location_title')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hours Title</label>
                        <input type="text" name="footer_hours_title" value="{{ old('footer_hours_title', data_get($settings, 'footer.hours_title')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bottom Origin</label>
                        <input type="text" name="footer_bottom_origin" value="{{ old('footer_bottom_origin', data_get($settings, 'footer.bottom_origin')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Copyright</label>
                        <input type="text" name="footer_copyright" value="{{ old('footer_copyright', data_get($settings, 'footer.copyright')) }}" class="form-control">
                    </div>
                </div>

                @foreach($footerLocations as $index => $location)
                    <div class="border rounded-3 p-3 {{ $index > 0 ? 'mt-3' : '' }}">
                        <h6 class="mb-3">{{ $index === 0 ? 'Primary Location' : 'Secondary Location' }}</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Label</label>
                                <input type="text" name="footer_locations[{{ $index }}][label]" value="{{ data_get($location, 'label') }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Map URL</label>
                                <input type="text" name="footer_locations[{{ $index }}][map_url]" value="{{ data_get($location, 'map_url') }}" class="form-control">
                            </div>
                            @foreach(data_get($location, 'lines', []) as $lineIndex => $line)
                                <div class="col-md-4">
                                    <label class="form-label">Address Line {{ $lineIndex + 1 }}</label>
                                    <input type="text" name="footer_locations[{{ $index }}][lines][{{ $lineIndex }}]" value="{{ $line }}" class="form-control">
                                </div>
                            @endforeach
                            @if($index === 0)
                                <div class="col-md-6">
                                    <label class="form-label">Weekday Label</label>
                                    <input type="text" name="footer_locations[{{ $index }}][weekday_label]" value="{{ data_get($location, 'weekday_label') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Weekday Value</label>
                                    <input type="text" name="footer_locations[{{ $index }}][weekday_value]" value="{{ data_get($location, 'weekday_value') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Weekend Label</label>
                                    <input type="text" name="footer_locations[{{ $index }}][weekend_label]" value="{{ data_get($location, 'weekend_label') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Weekend Value</label>
                                    <input type="text" name="footer_locations[{{ $index }}][weekend_value]" value="{{ data_get($location, 'weekend_value') }}" class="form-control">
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card {{ $isPresetOwned ? 'border-warning-subtle' : '' }}">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Navigation Structure</h5>
                    <p class="text-muted mb-0 small">
                        @if($isPresetOwned)
                            Advanced area for link structure, dropdown children, and megamenu schema.
                        @else
                            Link structure, dropdown children, and megamenu schema.
                        @endif
                    </p>
                </div>
                <span class="badge {{ $isPresetOwned ? 'bg-warning-subtle text-warning' : 'bg-light text-dark' }}">
                    {{ $isPresetOwned ? 'Advanced' : 'Structure' }}
                </span>
            </div>
            @if($isPresetOwned)
                <div class="card-body border-bottom pt-3 pb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="small text-muted">
                            Hidden by default for preset-owned chrome because this area is the fastest way to diverge from the original theme family.
                        </div>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-warning"
                            data-bs-toggle="collapse"
                            data-bs-target="#chrome-layout-advanced-structure"
                            aria-expanded="false"
                            aria-controls="chrome-layout-advanced-structure"
                        >
                            Toggle Advanced Structure
                        </button>
                    </div>
                </div>
            @endif
            <div class="card-body {{ $isPresetOwned ? 'collapse' : '' }}" id="{{ $isPresetOwned ? 'chrome-layout-advanced-structure' : '' }}">
                @if($isPresetOwned)
                    <div class="alert alert-light border small text-muted mb-4">
                        This area is where preset-owned chrome can diverge faster from the original theme family, especially for dropdown and mega menu structure.
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-1">Navigation Items</h6>
                        <div class="small text-muted">Type supports `link`, `button`, `dropdown`, and `megamenu`.</div>
                    </div>
                    <span class="badge bg-light text-dark">Up to 5 items</span>
                </div>
                <div class="row g-3">
                    @foreach($headerLinks as $index => $link)
                        @php
                            $itemType = data_get($link, 'type', 'link');
                            $itemChildrenJson = json_encode(data_get($link, 'children', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            $itemSectionsJson = json_encode(data_get($link, 'sections', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        @endphp
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Item Type {{ $index + 1 }}</label>
                                        <select name="header_links[{{ $index }}][type]" class="form-select">
                                            <option value="link" @selected($itemType === 'link')>Link</option>
                                            <option value="button" @selected($itemType === 'button')>Button</option>
                                            <option value="dropdown" @selected($itemType === 'dropdown')>Dropdown</option>
                                            <option value="megamenu" @selected($itemType === 'megamenu')>Mega Menu</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Label {{ $index + 1 }}</label>
                                        <input type="text" name="header_links[{{ $index }}][label]" value="{{ data_get($link, 'label') }}" class="form-control">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">URL {{ $index + 1 }}</label>
                                        <input type="text" name="header_links[{{ $index }}][url]" value="{{ data_get($link, 'url') }}" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Dropdown Children JSON</label>
                                        <textarea name="header_links[{{ $index }}][children_json]" rows="6" class="form-control font-monospace" placeholder='[{"label":"Overview","url":"/overview"}]'>{{ $itemChildrenJson === '[]' ? '' : $itemChildrenJson }}</textarea>
                                        <div class="form-text">Used for `dropdown`. Format: array of `{ label, url }`.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mega Menu Sections JSON</label>
                                        <textarea name="header_links[{{ $index }}][sections_json]" rows="6" class="form-control font-monospace" placeholder='[{"title":"Explore","links":[{"label":"Rooms","url":"/rooms"}]}]'>{{ $itemSectionsJson === '[]' ? '' : $itemSectionsJson }}</textarea>
                                        <div class="form-text">Used for `megamenu`. Format: array of `{ title, links: [{ label, url }] }`.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Live Preview</h5>
                    <p class="text-muted mb-0 small">Preview header and footer around a sample page scene without saving first.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" id="refresh-chrome-live-preview">Refresh</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="expand-chrome-live-preview">Expand</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label small">Preview Scene</label>
                        <select id="chrome_preview_scene" class="form-select form-select-sm">
                            <option value="landing">Landing</option>
                            <option value="story">Story</option>
                            <option value="minimal">Minimal</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="btn-group btn-group-sm w-100" role="group" aria-label="Chrome preview viewport">
                            <button type="button" class="btn btn-outline-secondary active" data-chrome-preview-viewport="desktop">Desktop</button>
                            <button type="button" class="btn btn-outline-secondary" data-chrome-preview-viewport="tablet">Tablet</button>
                            <button type="button" class="btn btn-outline-secondary" data-chrome-preview-viewport="mobile">Mobile</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="auto-refresh-chrome-preview" checked>
                            <label class="form-check-label small" for="auto-refresh-chrome-preview">Auto Refresh</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-3 small mb-3">
                    <span class="text-muted" id="chrome-live-preview-status">Loading preview...</span>
                    <span class="badge bg-light text-dark">Ads disabled in preview</span>
                </div>

                <div id="chrome-live-preview-shell" class="mx-auto" style="width: 100%; max-width: 100%;">
                    <div class="ratio ratio-16x9 rounded overflow-hidden border bg-light">
                        <iframe
                            id="chrome-live-preview-frame"
                            title="Chrome Layout Live Preview"
                            class="w-100 h-100 border-0 bg-white"
                        ></iframe>
                    </div>
                </div>

                <div class="small text-muted mt-3" id="chrome-live-preview-viewport-label">
                    Desktop preview width is active.
                </div>
            </div>
        </div>

        @if($isPresetOwned)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-1">Preset Editing Boundary</h5>
                    <p class="text-muted mb-0 small">This preset-owned chrome record supports safe content editing, but not unlimited theme restructuring.</p>
                </div>
                <div class="card-body">
                    <div class="vstack gap-3 small">
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">Safe Content Editing</div>
                            <div class="text-muted">Brand name, tagline, CTA label/url, top bar text, social links, journey links, contacts, location text, and footer copy.</div>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">Safe Visual Overrides</div>
                            <div class="text-muted">Navigation density, header surface, footer surface, header variant, and footer variant while the preset still fits the shared chrome schema.</div>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">Advanced / Custom Boundary</div>
                            <div class="text-muted">Do not assume this form can represent every future theme structure like arbitrary footer regions, unusual header compositions, or theme-specific mega menu schemas.</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $layout->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
                <div class="small text-muted mb-3">
                    Chrome Layout controls only header, navigation, and footer content. Styling comes from the selected Core Layout.
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">{{ $submitLabel }}</button>
                    <a href="{{ route('page-builder.chrome-layouts.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                </div>
            </div>
        </div>

        @if($layout->exists)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Record Info</h5>
                </div>
                <div class="card-body small text-muted">
                    <div class="mb-2"><strong>ID:</strong> {{ $layout->id }}</div>
                    <div class="mb-2"><strong>Created:</strong> {{ optional($layout->created_at)->format('d M Y H:i') ?: '-' }}</div>
                    <div><strong>Updated:</strong> {{ optional($layout->updated_at)->format('d M Y H:i') ?: '-' }}</div>
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
                    <p class="text-muted small mb-0">Choose an existing image or upload a new one.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="page-builder-media-search" class="form-label">Search Media</label>
                        <input type="text" id="page-builder-media-search" class="form-control" placeholder="Search by name or path">
                    </div>
                    <div class="col-md-4">
                        <label for="page-builder-media-upload" class="form-label">Upload Image</label>
                        <input type="file" id="page-builder-media-upload" class="form-control" accept="image/*">
                    </div>
                </div>
                <div id="page-builder-media-error" class="alert alert-danger d-none"></div>
                <div id="page-builder-media-grid" class="row g-3"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chrome-live-preview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Expanded Chrome Layout Preview</h5>
                    <div class="small text-muted">Use this view to inspect desktop navigation, dropdowns, and mega menu structure more realistically.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <iframe id="chrome-live-preview-modal-frame" class="w-100 h-100 border rounded bg-white" title="Expanded Chrome Layout Live Preview"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const hiddenInput = document.getElementById('header_brand_logo_url');
            const previewInput = document.getElementById('header_brand_logo_preview_input');
            const previewWrapper = document.getElementById('header_brand_logo_preview_wrapper');
            const previewImage = document.getElementById('header_brand_logo_preview');
            const browseButton = document.getElementById('open-layout-logo-media-modal');
            const clearButton = document.getElementById('clear-layout-logo-media');
            const mediaModalElement = document.getElementById('page-builder-media-modal');
            const mediaSearchInput = document.getElementById('page-builder-media-search');
            const mediaUploadInput = document.getElementById('page-builder-media-upload');
            const mediaGrid = document.getElementById('page-builder-media-grid');
            const mediaError = document.getElementById('page-builder-media-error');

            if (!hiddenInput || !previewInput || !browseButton || !clearButton || !mediaModalElement) {
                return;
            }

            const mediaIndexUrl = @js(route('page-builder.media.index'));
            const mediaUploadUrl = @js(route('page-builder.media.store'));
            const chromePreviewUrl = @js($chromePreviewUrl);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const mediaModal = window.bootstrap ? new window.bootstrap.Modal(mediaModalElement) : null;
            const chromePreviewFrame = document.getElementById('chrome-live-preview-frame');
            const chromePreviewShell = document.getElementById('chrome-live-preview-shell');
            const chromePreviewStatus = document.getElementById('chrome-live-preview-status');
            const chromePreviewViewportLabel = document.getElementById('chrome-live-preview-viewport-label');
            const refreshChromePreviewButton = document.getElementById('refresh-chrome-live-preview');
            const expandChromePreviewButton = document.getElementById('expand-chrome-live-preview');
            const chromePreviewModalElement = document.getElementById('chrome-live-preview-modal');
            const chromePreviewModalFrame = document.getElementById('chrome-live-preview-modal-frame');
            const chromePreviewModal = chromePreviewModalElement ? new window.bootstrap.Modal(chromePreviewModalElement) : null;
            const autoRefreshChromePreview = document.getElementById('auto-refresh-chrome-preview');
            const chromePreviewScene = document.getElementById('chrome_preview_scene');
            const previewViewportButtons = Array.from(document.querySelectorAll('[data-chrome-preview-viewport]'));
            const pageForm = chromePreviewFrame ? chromePreviewFrame.closest('form') : null;
            let chromePreviewRequestId = 0;
            let chromePreviewDebounce = null;
            let isChromeAutoRefreshEnabled = autoRefreshChromePreview ? autoRefreshChromePreview.checked : true;

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

            const setChromePreviewStatus = (message, tone = 'muted') => {
                if (!chromePreviewStatus) {
                    return;
                }

                chromePreviewStatus.className = '';

                if (tone === 'danger') {
                    chromePreviewStatus.classList.add('text-danger');
                } else if (tone === 'success') {
                    chromePreviewStatus.classList.add('text-success');
                } else {
                    chromePreviewStatus.classList.add('text-muted');
                }

                chromePreviewStatus.textContent = message;
            };

            const setChromePreviewViewport = (viewport) => {
                const config = previewViewportMap[viewport] || previewViewportMap.desktop;

                if (chromePreviewShell) {
                    chromePreviewShell.style.maxWidth = config.width;
                }

                if (chromePreviewViewportLabel) {
                    chromePreviewViewportLabel.textContent = config.label;
                }

                previewViewportButtons.forEach((button) => {
                    button.classList.toggle('active', button.dataset.chromePreviewViewport === viewport);
                });
            };

            const refreshChromePreview = async () => {
                if (!pageForm || !chromePreviewFrame) {
                    return;
                }

                const requestId = ++chromePreviewRequestId;
                const formData = new FormData(pageForm);
                formData.delete('_method');
                formData.set('preview_scene', chromePreviewScene?.value || 'landing');

                setChromePreviewStatus('Updating preview...', 'muted');

                try {
                    const response = await fetch(chromePreviewUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Preview request failed.');
                    }

                    const html = await response.text();

                    if (requestId !== chromePreviewRequestId) {
                        return;
                    }

                    chromePreviewFrame.srcdoc = html;
                    if (chromePreviewModalFrame) {
                        chromePreviewModalFrame.srcdoc = html;
                    }
                    setChromePreviewStatus('Preview updated.', 'success');
                } catch (error) {
                    setChromePreviewStatus(error.message || 'Preview failed to load.', 'danger');
                }
            };

            const queueChromePreviewRefresh = () => {
                if (!chromePreviewFrame) {
                    return;
                }

                if (!isChromeAutoRefreshEnabled) {
                    setChromePreviewStatus('Preview paused. Use Refresh to reload.', 'muted');
                    return;
                }

                window.clearTimeout(chromePreviewDebounce);
                chromePreviewDebounce = window.setTimeout(refreshChromePreview, 450);
            };

            const setPreview = (path, url) => {
                hiddenInput.value = path || '';
                previewInput.value = path || '';

                if (previewImage) {
                    previewImage.src = url || '';
                }

                if (previewWrapper) {
                    previewWrapper.style.display = path ? '' : 'none';
                }

                queueChromePreviewRefresh();
            };

            const showError = (message) => {
                if (!mediaError) {
                    return;
                }

                if (message) {
                    mediaError.textContent = message;
                    mediaError.classList.remove('d-none');
                } else {
                    mediaError.textContent = '';
                    mediaError.classList.add('d-none');
                }
            };

            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const renderMedia = (items) => {
                if (!mediaGrid) {
                    return;
                }

                if (!items.length) {
                    mediaGrid.innerHTML = '<div class="col-12"><div class="text-center text-muted py-5 border rounded">No media found.</div></div>';
                    return;
                }

                mediaGrid.innerHTML = items.map((item) => `
                    <div class="col-md-4 col-xl-3">
                        <button
                            type="button"
                            class="btn btn-light border w-100 h-100 text-start p-2"
                            data-media-path="${escapeHtml(item.path)}"
                            data-media-url="${escapeHtml(item.url)}"
                        >
                            <div class="ratio ratio-4x3 bg-light rounded overflow-hidden mb-2">
                                ${item.is_image ? `<img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.name)}" class="w-100 h-100 object-fit-cover">` : '<div class="d-flex align-items-center justify-content-center text-muted">No preview</div>'}
                            </div>
                            <div class="fw-semibold small text-truncate">${escapeHtml(item.name)}</div>
                            <div class="text-muted small text-truncate">${escapeHtml(item.path)}</div>
                        </button>
                    </div>
                `).join('');

                mediaGrid.querySelectorAll('[data-media-path]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const path = button.getAttribute('data-media-path') || '';
                        const url = button.getAttribute('data-media-url') || '';
                        setPreview(path, url);
                        mediaModal?.hide();
                    });
                });
            };

            const loadMedia = async () => {
                showError(null);

                try {
                    const url = new URL(mediaIndexUrl, window.location.origin);
                    url.searchParams.set('json', '1');

                    if (mediaSearchInput?.value) {
                        url.searchParams.set('q', mediaSearchInput.value);
                    }

                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load media.');
                    }

                    const payload = await response.json();
                    renderMedia(Array.isArray(payload.media) ? payload.media : []);
                } catch (error) {
                    showError(error.message || 'Unable to load media.');
                }
            };

            const uploadMedia = async (file) => {
                if (!file) {
                    return;
                }

                showError(null);

                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch(mediaUploadUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                        body: formData,
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.media) {
                        throw new Error(payload.message || 'Unable to upload media.');
                    }

                    setPreview(payload.media.path, payload.media.url);
                    await loadMedia();
                } catch (error) {
                    showError(error.message || 'Unable to upload media.');
                } finally {
                    mediaUploadInput.value = '';
                }
            };

            browseButton.addEventListener('click', async () => {
                mediaModal?.show();
                await loadMedia();
            });

            clearButton.addEventListener('click', () => setPreview('', ''));
            refreshChromePreviewButton?.addEventListener('click', refreshChromePreview);
            expandChromePreviewButton?.addEventListener('click', () => {
                if (chromePreviewModalFrame && chromePreviewFrame) {
                    chromePreviewModalFrame.srcdoc = chromePreviewFrame.srcdoc || '';
                }

                chromePreviewModal?.show();
            });
            autoRefreshChromePreview?.addEventListener('change', function () {
                isChromeAutoRefreshEnabled = this.checked;

                if (isChromeAutoRefreshEnabled) {
                    queueChromePreviewRefresh();
                } else {
                    setChromePreviewStatus('Preview paused. Use Refresh to reload.', 'muted');
                }
            });
            chromePreviewScene?.addEventListener('change', queueChromePreviewRefresh);
            previewViewportButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    setChromePreviewViewport(this.dataset.chromePreviewViewport || 'desktop');
                });
            });

            mediaSearchInput?.addEventListener('input', () => {
                clearTimeout(window.__pbChromeLayoutMediaSearchTimer);
                window.__pbChromeLayoutMediaSearchTimer = setTimeout(loadMedia, 250);
            });

            mediaUploadInput?.addEventListener('change', async (event) => {
                const file = event.target.files?.[0];
                await uploadMedia(file);
            });

            pageForm?.querySelectorAll('input, textarea, select').forEach((field) => {
                if (field.id === 'page-builder-media-search' || field.id === 'page-builder-media-upload') {
                    return;
                }

                const eventName = ['checkbox', 'radio', 'select-one'].includes(field.type) || field.tagName === 'SELECT'
                    ? 'change'
                    : 'input';

                field.addEventListener(eventName, queueChromePreviewRefresh);
            });

            setChromePreviewViewport('desktop');
            queueChromePreviewRefresh();
        })();
    </script>
@endpush
