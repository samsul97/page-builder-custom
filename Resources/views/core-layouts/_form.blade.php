@php
    $settings = \App\Models\PageBuilder\PageBuilderCoreLayout::mergeSettings($coreLayout->settings);
    $presetKey = $presetKey ?? data_get($coreLayout->settings, 'preset.key');
    $presetName = data_get($coreLayout->settings, 'preset.name');
    $isPresetOwned = filled($presetKey);
    $corePreviewUrl = route('page-builder.core-layouts.preview');
    $fontReferences = [
        '"Plus Jakarta Sans", sans-serif',
        '"Inter", sans-serif',
        '"DM Sans", sans-serif',
        '"Manrope", sans-serif',
        '"Fraunces", serif',
        '"Playfair Display", serif',
        '"Cormorant Garamond", serif',
        '"Libre Baskerville", serif',
    ];
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Core Layout Details</h5>
            </div>
            <div class="card-body">
                @if($isPresetOwned)
                    <div class="alert alert-light border mb-4">
                        <div class="fw-semibold mb-1">Preset-Owned Core Layout</div>
                        <div class="small text-muted mb-2">This core layout belongs to a preset family. Design tokens can still be edited, but the record identity should stay stable.</div>
                        <a href="{{ route('page-builder.presets.show', $presetKey) }}" class="btn btn-sm btn-outline-secondary">Open Preset</a>
                    </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', $coreLayout->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Key</label>
                        @if($isPresetOwned)
                            <input type="hidden" name="key" value="{{ old('key', $coreLayout->key) }}">
                        @endif
                        <input type="text" name="key" value="{{ old('key', $coreLayout->key) }}" class="form-control @error('key') is-invalid @enderror" required {{ $isPresetOwned ? 'disabled' : '' }}>
                        <div class="form-text">
                            @if($isPresetOwned)
                                Key is locked because this record is owned by preset <code>{{ $presetKey }}</code>.
                            @else
                                Key is auto-generated from the name, but you can still refine it if needed.
                            @endif
                        </div>
                    </div>
                    @if($isPresetOwned)
                        <div class="col-md-6">
                            <label class="form-label">Preset Family</label>
                            <input type="text" value="{{ $presetName ?: $presetKey }}" class="form-control" disabled>
                            <input type="hidden" name="preview_preset_key" value="{{ $presetKey }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ownership</label>
                            <input type="text" value="Preset Theme" class="form-control" disabled>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label">Font Family</label>
                        <input type="text" name="font_family" list="page-builder-font-family-options" value="{{ old('font_family', data_get($settings, 'font_family')) }}" class="form-control @error('font_family') is-invalid @enderror" required>
                        <div class="form-text">Examples: <code>"Plus Jakarta Sans", sans-serif</code>, <code>"Inter", sans-serif</code>. Use a full CSS font-family string.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Heading Font Family</label>
                        <input type="text" name="heading_font_family" list="page-builder-font-family-options" value="{{ old('heading_font_family', data_get($settings, 'heading_font_family')) }}" class="form-control @error('heading_font_family') is-invalid @enderror" required>
                        <div class="form-text">Examples: <code>"Fraunces", serif</code>, <code>"Playfair Display", serif</code>. Use a full CSS font-family string.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Button Radius</label>
                        <input type="text" name="button_radius" value="{{ old('button_radius', data_get($settings, 'button_radius')) }}" class="form-control @error('button_radius') is-invalid @enderror" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Container Width</label>
                        <input type="text" name="container_width" value="{{ old('container_width', data_get($settings, 'container_width')) }}" class="form-control @error('container_width') is-invalid @enderror" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Section Spacing</label>
                        <input type="text" name="section_spacing" value="{{ old('section_spacing', data_get($settings, 'section_spacing')) }}" class="form-control @error('section_spacing') is-invalid @enderror" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Color Tokens</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label">Background</label>
                        <input type="color" name="background_color" value="{{ old('background_color', data_get($settings, 'background_color')) }}" class="form-control form-control-color">
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label">Card</label>
                        <input type="color" name="card_color" value="{{ old('card_color', data_get($settings, 'card_color')) }}" class="form-control form-control-color">
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label">Accent</label>
                        <input type="color" name="accent_color" value="{{ old('accent_color', data_get($settings, 'accent_color')) }}" class="form-control form-control-color">
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label">Text</label>
                        <input type="color" name="text_color" value="{{ old('text_color', data_get($settings, 'text_color')) }}" class="form-control form-control-color">
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label">Muted Text</label>
                        <input type="color" name="muted_text_color" value="{{ old('muted_text_color', data_get($settings, 'muted_text_color')) }}" class="form-control form-control-color">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-1">Live Preview</h5>
                    <p class="text-muted mb-0 small">
                        @if($isPresetOwned)
                            Preview core design tokens against the preset family chrome baseline, without saving first.
                        @else
                            Preview core design tokens against a sample landing scene without saving first.
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" id="refresh-core-live-preview">Refresh</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="expand-core-live-preview">Expand</button>
                </div>
            </div>
            <div class="card-body">
                <div class="btn-group btn-group-sm w-100 mb-3" role="group" aria-label="Core preview viewport">
                    <button type="button" class="btn btn-outline-secondary active" data-core-preview-viewport="desktop">Desktop</button>
                    <button type="button" class="btn btn-outline-secondary" data-core-preview-viewport="tablet">Tablet</button>
                    <button type="button" class="btn btn-outline-secondary" data-core-preview-viewport="mobile">Mobile</button>
                </div>
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-sm-7">
                        <label for="core_preview_scene" class="form-label small text-muted mb-1">Preview Scene</label>
                        <select id="core_preview_scene" class="form-select form-select-sm">
                            <option value="landing">Landing</option>
                            <option value="story">Story</option>
                            <option value="minimal">Minimal</option>
                        </select>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-check form-switch pt-4 mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="auto-refresh-core-preview" checked>
                            <label class="form-check-label small" for="auto-refresh-core-preview">Auto Refresh</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center gap-3 small mb-3">
                    <span class="text-muted" id="core-live-preview-status">Loading preview...</span>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        @if($isPresetOwned)
                            <span class="badge bg-primary-subtle text-primary">Theme-aware via {{ $presetName ?: $presetKey }}</span>
                        @endif
                        <span class="badge bg-light text-dark">Ads disabled in preview</span>
                    </div>
                </div>
                <div id="core-live-preview-shell" class="mx-auto" style="width: 100%; max-width: 100%;">
                    <div class="ratio ratio-16x9 rounded overflow-hidden border bg-light">
                        <iframe id="core-live-preview-frame" class="w-100 h-100 border-0 bg-white" title="Core Layout Live Preview"></iframe>
                    </div>
                </div>
                <div class="small text-muted mt-3" id="core-live-preview-viewport-label">Desktop preview width is active.</div>
                @if($isPresetOwned)
                    <div class="small text-muted mt-2">This preview reuses the preset family header/footer baseline so token changes can be judged in a more realistic theme context.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1">Font Reference</h5>
                <p class="text-muted mb-0 small">Quick reference stacks for body and heading pairing.</p>
            </div>
            <div class="card-body">
                <div class="vstack gap-3 small">
                    @foreach($fontReferences as $fontReference)
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">{{ $fontReference }}</div>
                            <div class="text-muted">Paste this directly into body or heading font fields if it matches your intended tone.</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $coreLayout->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
                <div class="small text-muted mb-3">
                    Core Layout controls styling tokens only. Header, navigation, and footer belong to Chrome Layout.
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">{{ $submitLabel }}</button>
                    <a href="{{ route('page-builder.core-layouts.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<datalist id="page-builder-font-family-options">
    @foreach($fontReferences as $fontReference)
        <option value="{{ $fontReference }}"></option>
    @endforeach
</datalist>

<div class="modal fade" id="core-live-preview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Expanded Core Layout Preview</h5>
                    <div class="small text-muted">Use this to inspect typography, spacing, and color tokens with more realistic width.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <iframe id="core-live-preview-modal-frame" class="w-100 h-100 border rounded bg-white" title="Expanded Core Layout Live Preview"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const corePreviewUrl = @js($corePreviewUrl);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const corePreviewFrame = document.getElementById('core-live-preview-frame');
            const corePreviewShell = document.getElementById('core-live-preview-shell');
            const corePreviewStatus = document.getElementById('core-live-preview-status');
            const corePreviewViewportLabel = document.getElementById('core-live-preview-viewport-label');
            const refreshCorePreviewButton = document.getElementById('refresh-core-live-preview');
            const expandCorePreviewButton = document.getElementById('expand-core-live-preview');
            const corePreviewModalElement = document.getElementById('core-live-preview-modal');
            const corePreviewModalFrame = document.getElementById('core-live-preview-modal-frame');
            const corePreviewModal = corePreviewModalElement ? new window.bootstrap.Modal(corePreviewModalElement) : null;
            const previewViewportButtons = Array.from(document.querySelectorAll('[data-core-preview-viewport]'));
            const corePreviewScene = document.getElementById('core_preview_scene');
            const autoRefreshCorePreview = document.getElementById('auto-refresh-core-preview');
            const pageForm = corePreviewFrame ? corePreviewFrame.closest('form') : null;
            let corePreviewRequestId = 0;
            let corePreviewDebounce = null;
            let isAutoRefreshCorePreviewEnabled = autoRefreshCorePreview ? autoRefreshCorePreview.checked : true;

            const previewViewportMap = {
                desktop: { width: '100%', label: 'Desktop preview width is active.' },
                tablet: { width: '820px', label: 'Tablet preview width is active.' },
                mobile: { width: '430px', label: 'Mobile preview width is active.' },
            };

            const setCorePreviewStatus = (message, tone = 'muted') => {
                if (!corePreviewStatus) {
                    return;
                }

                corePreviewStatus.className = '';
                corePreviewStatus.classList.add(tone === 'danger' ? 'text-danger' : tone === 'success' ? 'text-success' : 'text-muted');
                corePreviewStatus.textContent = message;
            };

            const setCorePreviewViewport = (viewport) => {
                const config = previewViewportMap[viewport] || previewViewportMap.desktop;

                if (corePreviewShell) {
                    corePreviewShell.style.maxWidth = config.width;
                }

                if (corePreviewViewportLabel) {
                    corePreviewViewportLabel.textContent = config.label;
                }

                previewViewportButtons.forEach((button) => {
                    button.classList.toggle('active', button.dataset.corePreviewViewport === viewport);
                });
            };

            const refreshCorePreview = async () => {
                if (!pageForm || !corePreviewFrame) {
                    return;
                }

                const requestId = ++corePreviewRequestId;
                const formData = new FormData(pageForm);
                formData.delete('_method');
                formData.set('preview_scene', corePreviewScene?.value || 'landing');

                setCorePreviewStatus('Updating preview...', 'muted');

                try {
                    const response = await fetch(corePreviewUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Preview request failed.');
                    }

                    const html = await response.text();

                    if (requestId !== corePreviewRequestId) {
                        return;
                    }

                    corePreviewFrame.srcdoc = html;
                    if (corePreviewModalFrame) {
                        corePreviewModalFrame.srcdoc = html;
                    }
                    setCorePreviewStatus('Preview updated.', 'success');
                } catch (error) {
                    setCorePreviewStatus(error.message || 'Preview failed to load.', 'danger');
                }
            };

            const queueCorePreviewRefresh = (delay = 350, statusMessage = 'Updating preview...') => {
                if (!isAutoRefreshCorePreviewEnabled) {
                    setCorePreviewStatus('Preview paused. Use Refresh to reload.', 'muted');
                    return;
                }

                window.clearTimeout(corePreviewDebounce);
                setCorePreviewStatus(statusMessage, 'muted');
                corePreviewDebounce = window.setTimeout(refreshCorePreview, delay);
            };

            refreshCorePreviewButton?.addEventListener('click', refreshCorePreview);
            expandCorePreviewButton?.addEventListener('click', () => {
                if (corePreviewModalFrame && corePreviewFrame) {
                    corePreviewModalFrame.srcdoc = corePreviewFrame.srcdoc || '';
                }

                corePreviewModal?.show();
            });

            previewViewportButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    setCorePreviewViewport(this.dataset.corePreviewViewport || 'desktop');
                });
            });

            autoRefreshCorePreview?.addEventListener('change', function () {
                isAutoRefreshCorePreviewEnabled = this.checked;

                if (isAutoRefreshCorePreviewEnabled) {
                    queueCorePreviewRefresh();
                } else {
                    setCorePreviewStatus('Preview paused. Use Refresh to reload.', 'muted');
                }
            });

            corePreviewScene?.addEventListener('change', () => {
                queueCorePreviewRefresh(250, 'Changing preview scene...');
            });

            pageForm?.querySelectorAll('input, select').forEach((field) => {
                const eventName = ['checkbox', 'radio', 'color'].includes(field.type) || field.tagName === 'SELECT'
                    ? 'change'
                    : 'input';

                field.addEventListener(eventName, queueCorePreviewRefresh);
            });

            setCorePreviewViewport('desktop');
            queueCorePreviewRefresh();
        })();
    </script>
@endpush
