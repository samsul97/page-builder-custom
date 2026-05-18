@extends('layouts.app')

@section('title', 'Plugins / Theme')

@section('breadcrumbs', Breadcrumbs::render('page-builder.plugins-theme.index'))

@php
    $statusBadgeMap = [
        'enabled' => 'bg-success-subtle text-success',
        'disabled' => 'bg-secondary-subtle text-secondary',
        'planned' => 'bg-warning-subtle text-warning',
    ];
    $categoryHelpMap = [
        'theme' => [
            'title' => 'Theme = template family',
            'description' => 'Theme items represent a design family or preset family such as Rawdee main site style.',
        ],
        'block' => [
            'title' => 'Block = reusable page section pack',
            'description' => 'Block items represent reusable section packs such as hero, CTA, gallery, or feature-grid patterns.',
        ],
        'system' => [
            'title' => 'System = builder behavior support',
            'description' => 'System items document builder capabilities and readiness. They are not destructive feature toggles unless a feature is explicitly wired to them.',
        ],
    ];
@endphp

@section('content')
    <div class="row g-4">
        @if($errors->has('activation'))
            <div class="col-12">
                <div class="alert alert-danger mb-0">
                    {{ $errors->first('activation') }}
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                        <div>
                            <span class="badge bg-dark-subtle text-dark text-uppercase mb-3">Curated Library</span>
                            <h4 class="mb-2">Plugins / Theme</h4>
                            <p class="text-muted mb-0">
                                Internal categorized library for theme families and plugin packs. This is the curated layer that comes after presets, not a free-form marketplace.
                            </p>
                        </div>
                        <div class="alert alert-light border mb-0 py-3 px-3">
                            <div class="fw-semibold mb-1">Current scope</div>
                            <p class="small text-muted mb-0">Catalog, category grouping, curated registration, and enable/disable state are live. This still manages internal builder assets, not package.json auto-discovery yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($presetFilter))
            <div class="col-12">
                <div class="alert alert-primary-subtle border border-primary-subtle d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-0">
                    <div>
                        <div class="fw-semibold mb-1">Preset Filter Active</div>
                        <div class="small text-muted">Showing only library assets related to preset <code>{{ $presetFilter }}</code>.</div>
                    </div>
                    <a href="{{ route('page-builder.plugins-theme.index') }}" class="btn btn-sm btn-light">Clear Filter</a>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">How To Read This Page</h5>
                    <p class="text-muted mb-0">These labels describe what each library category means inside the current builder product.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        @foreach($categoryHelpMap as $help)
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-semibold mb-1">{{ data_get($help, 'title') }}</div>
                                    <div class="small text-muted">{{ data_get($help, 'description') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Register Internal Asset</h5>
                    <p class="text-muted mb-0">Curated registration flow for internal themes and plugin packs. This registers metadata only, not arbitrary code uploads or package.json scanning.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="{{ route('page-builder.plugins-theme.store') }}" method="POST" class="vstack gap-3">
                        @csrf
                        <div>
                            <label for="library_name" class="form-label">Name</label>
                            <input type="text" id="library_name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="library_key" class="form-label">Key</label>
                            <input type="text" id="library_key" name="key" value="{{ old('key') }}" class="form-control @error('key') is-invalid @enderror" placeholder="optional-custom-key">
                            <div class="form-text">Leave empty to generate from category and name.</div>
                            @error('key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="library_type" class="form-label">Type</label>
                                <select id="library_type" name="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="theme" @selected(old('type') === 'theme')>Theme</option>
                                    <option value="plugin" @selected(old('type', 'plugin') === 'plugin')>Plugin</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="library_category" class="form-label">Category</label>
                                <select id="library_category" name="category" class="form-select @error('category') is-invalid @enderror">
                                    <option value="theme" @selected(old('category') === 'theme')>Theme</option>
                                    <option value="block" @selected(old('category', 'block') === 'block')>Block</option>
                                    <option value="system" @selected(old('category') === 'system')>System</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="library_status" class="form-label">Initial Status</label>
                            <select id="library_status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="planned" @selected(old('status', 'planned') === 'planned')>Planned</option>
                                <option value="enabled" @selected(old('status') === 'enabled')>Enabled</option>
                                <option value="disabled" @selected(old('status') === 'disabled')>Disabled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="library_related_preset_key" class="form-label">Related Preset Key</label>
                            <input type="text" id="library_related_preset_key" name="related_preset_key" value="{{ old('related_preset_key') }}" class="form-control @error('related_preset_key') is-invalid @enderror" placeholder="rawdee-main-site">
                            @error('related_preset_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="library_description" class="form-label">Description</label>
                            <textarea id="library_description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Register Asset</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Import Manifest</h5>
                    <p class="text-muted mb-0">Phase H+ import path for curated theme/plugin metadata. This does not execute package code yet.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="{{ route('page-builder.plugins-theme.import-manifest') }}" method="POST" enctype="multipart/form-data" class="vstack gap-3">
                        @csrf
                        <div>
                            <label for="library_manifest_file" class="form-label">Manifest File</label>
                            <input
                                type="file"
                                id="library_manifest_file"
                                name="manifest_file"
                                accept="application/json,.json"
                                class="form-control @error('manifest_file') is-invalid @enderror"
                            >
                            <div class="form-text">Upload a curated `.json` manifest, or paste JSON below.</div>
                            @error('manifest_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="library_manifest_json" class="form-label">Manifest JSON</label>
                            <textarea
                                id="library_manifest_json"
                                name="manifest_json"
                                rows="12"
                                class="form-control font-monospace @error('manifest_json') is-invalid @enderror"
                                placeholder='{"items":[{"key":"block-pack-school-campaign","name":"School Campaign Block Pack","type":"plugin","category":"block","status":"enabled","description":"Reusable campaign sections for school landing pages.","related_preset_key":"school-profile","package":"@rawdee/block-pack-school-campaign","version":"0.1.0","activation":{"contract":"block_pack","block_types":["hero","cta","feature_grid"],"reusable_blocks":[{"key":"school-hero","name":"School Hero","description":"Starter hero for school campaigns.","blocks":[{"type":"hero","data":{"eyebrow":"Admissions Open","title":"Build a school landing page","subtitle":"Use this starter block as a first campaign section.","button_label":"Register Now","button_url":"#"}}]}]}}]}'
                            >{{ old('manifest_json') }}</textarea>
                            <div class="form-text">Theme items create preset blueprints automatically. Block/plugin items may include an `activation` contract for reusable block recipes.</div>
                            @error('manifest_json')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-outline-primary">Import Manifest</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Block Type Registry</h5>
                    <p class="text-muted mb-0">Enable or disable built-in editor block components such as hero, video, gallery, FAQ, form, and dynamic collection.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="alert alert-light border small mb-4">
                        Disabling a block type removes it from the editor insert panel. Existing saved pages are preserved and will show a warning if they still use that block type.
                    </div>
                    <div class="vstack gap-3">
                        @foreach($groupedBlockTypes ?? collect() as $blockCategory => $categoryBlockTypes)
                            <div>
                                <div class="fw-semibold text-capitalize mb-2">{{ str_replace('_', ' ', $blockCategory) }}</div>
                                <div class="vstack gap-2">
                                    @foreach($categoryBlockTypes as $blockType)
                                        <div class="border rounded-3 p-3">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <span class="fw-semibold">{{ data_get($blockType, 'label') }}</span>
                                                        <span class="badge {{ $statusBadgeMap[data_get($blockType, 'status')] ?? 'bg-light text-dark' }}">
                                                            {{ ucfirst(data_get($blockType, 'status')) }}
                                                        </span>
                                                    </div>
                                                    <div class="small text-muted mb-1">{{ data_get($blockType, 'description') }}</div>
                                                    <code class="small">{{ data_get($blockType, 'type') }}</code>
                                                </div>
                                                <form action="{{ route('page-builder.plugins-theme.block-types.toggle', data_get($blockType, 'type')) }}" method="POST">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm {{ data_get($blockType, 'status') === 'enabled' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                    >
                                                        {{ data_get($blockType, 'status') === 'enabled' ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Library Summary</h5>
                    <p class="text-muted mb-0">This keeps the future plugin/theme surface concrete without turning it into a marketplace too early.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Items</div>
                                <div class="fw-semibold">{{ $items->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Categories</div>
                                <div class="fw-semibold">{{ $groupedItems->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Enabled</div>
                                <div class="fw-semibold">{{ $items->where('status', 'enabled')->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Disabled</div>
                                <div class="fw-semibold">{{ $items->where('status', 'disabled')->count() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 vstack gap-3">
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">Order of use</div>
                            <p class="small text-muted mb-0">Preset first, library second, import flow later.</p>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">Current categories</div>
                            <p class="small text-muted mb-0">Theme = template family. Block = reusable page section pack. System = builder behavior support.</p>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">Related preset</div>
                            <p class="small text-muted mb-0">The first enabled theme item should stay aligned with the internal baseline preset. Use preset filter to focus on one family at a time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="vstack gap-4">
                @foreach($groupedItems as $category => $categoryItems)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="mb-1 text-capitalize">{{ $category }}</h5>
                            <p class="text-muted mb-0">
                                {{ data_get($categoryHelpMap, $category . '.description', 'Curated assets currently tracked under this category.') }}
                            </p>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="row g-3">
                                @foreach($categoryItems as $item)
                                    @php
                                        $activation = data_get($item, 'activation', []);
                                        $activationContract = data_get($activation, 'contract');
                                        $activationState = data_get($activations ?? [], data_get($item, 'key'));
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <span class="badge {{ $statusBadgeMap[data_get($item, 'status')] ?? 'bg-light text-dark' }}">
                                                    {{ ucfirst(data_get($item, 'status', 'draft')) }}
                                                </span>
                                                <span class="badge bg-light text-dark text-uppercase">
                                                    {{ data_get($item, 'type', 'item') }}
                                                </span>
                                                @if(data_get($item, 'is_custom'))
                                                    <span class="badge bg-info-subtle text-info">Custom</span>
                                                @endif
                                            </div>
                                            <h6 class="mb-2">{{ data_get($item, 'name') }}</h6>
                                            <p class="text-muted small mb-3">{{ data_get($item, 'description') }}</p>

                                            <dl class="row mb-0 small">
                                                <dt class="col-4 text-muted">Key</dt>
                                                <dd class="col-8"><code>{{ data_get($item, 'key') }}</code></dd>

                                                <dt class="col-4 text-muted">Source</dt>
                                                <dd class="col-8 text-capitalize">{{ data_get($item, 'source', '-') }}</dd>

                                                @if(data_get($item, 'package'))
                                                    <dt class="col-4 text-muted">Package</dt>
                                                    <dd class="col-8"><code>{{ data_get($item, 'package') }}</code></dd>
                                                @endif

                                                @if(data_get($item, 'version'))
                                                    <dt class="col-4 text-muted">Version</dt>
                                                    <dd class="col-8">{{ data_get($item, 'version') }}</dd>
                                                @endif

                                                <dt class="col-4 text-muted">Preset</dt>
                                                <dd class="col-8">
                                                    @if(data_get($item, 'related_preset_key'))
                                                        <a href="{{ route('page-builder.presets.show', data_get($item, 'related_preset_key')) }}">
                                                            {{ data_get($item, 'related_preset_key') }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </dd>

                                                <dt class="col-4 text-muted">Default</dt>
                                                <dd class="col-8 text-capitalize">{{ data_get($item, 'default_status', '-') }}</dd>
                                            </dl>

                                            @if($activationContract)
                                                <div class="border rounded-3 p-3 mt-3 bg-light">
                                                    <div class="fw-semibold small mb-2">Activation Contract</div>
                                                    <dl class="row mb-0 small">
                                                        <dt class="col-4 text-muted">Contract</dt>
                                                        <dd class="col-8"><code>{{ $activationContract }}</code></dd>

                                                        <dt class="col-4 text-muted">Block Types</dt>
                                                        <dd class="col-8">
                                                            @forelse(data_get($activation, 'block_types', []) as $blockType)
                                                                <span class="badge bg-white text-dark border me-1 mb-1">{{ $blockType }}</span>
                                                            @empty
                                                                <span class="text-muted">-</span>
                                                            @endforelse
                                                        </dd>

                                                        <dt class="col-4 text-muted">Recipes</dt>
                                                        <dd class="col-8">{{ count(data_get($activation, 'reusable_blocks', [])) }} reusable block(s)</dd>

                                                        @if($activationState)
                                                            <dt class="col-4 text-muted">Last Run</dt>
                                                            <dd class="col-8">
                                                                {{ data_get($activationState, 'activated_at') }}
                                                                <div class="text-muted">
                                                                    Created {{ data_get($activationState, 'created_reusable_blocks', 0) }},
                                                                    skipped {{ data_get($activationState, 'skipped_reusable_blocks', 0) }}.
                                                                </div>
                                                            </dd>
                                                        @endif
                                                    </dl>
                                                </div>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
                                                <div class="small text-muted">
                                                    Current state: <strong class="text-capitalize">{{ data_get($item, 'status', 'planned') }}</strong>
                                                </div>
                                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                                    @if($activationContract)
                                                        <form action="{{ route('page-builder.plugins-theme.activate', data_get($item, 'key')) }}" method="POST">
                                                            @csrf
                                                            <button
                                                                type="submit"
                                                                class="btn btn-sm btn-outline-primary"
                                                                @disabled(data_get($item, 'status') !== 'enabled')
                                                            >
                                                                Activate
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <form action="{{ route('page-builder.plugins-theme.toggle', data_get($item, 'key')) }}" method="POST">
                                                        @csrf
                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm {{ data_get($item, 'status') === 'enabled' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        >
                                                            {{ data_get($item, 'status') === 'enabled' ? 'Disable' : 'Enable' }}
                                                        </button>
                                                    </form>
                                                    @if(data_get($item, 'is_custom'))
                                                        <form action="{{ route('page-builder.plugins-theme.destroy', data_get($item, 'key')) }}" method="POST" onsubmit="return confirm('Remove this custom library asset?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Remove</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
