@extends('layouts.app')

@section('title', data_get($preset, 'name', 'Preset Blueprint'))

@section('breadcrumbs', Breadcrumbs::render('page-builder.presets.show', data_get($preset, 'key')))

@section('content')
    @php($presetInstantiation = session('presetInstantiation'))
    @php($allLibraryItems = $allLibraryItems ?? $relatedLibraryItems ?? collect())
    @php($relatedLibraryItems = $relatedLibraryItems ?? collect())
    @php($enabledRelatedLibraryItems = $enabledRelatedLibraryItems ?? collect())
    @php($recommendedAssetKeys = $recommendedAssetKeys ?? collect())
    @php($missingRecommendedAssets = $missingRecommendedAssets ?? collect())

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                        <div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-primary-subtle text-primary">{{ ucfirst(data_get($preset, 'status', 'draft')) }}</span>
                                <span class="badge bg-light text-dark">{{ data_get($preset, 'family.theme_name', 'Preset Family') }}</span>
                            </div>
                            <h4 class="mb-2">{{ data_get($preset, 'name') }}</h4>
                            <p class="text-muted mb-0">{{ data_get($preset, 'description') }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <form action="{{ route('page-builder.presets.instantiate', data_get($preset, 'key')) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Instantiate Preset</button>
                            </form>
                            <a href="{{ route('page-builder.presets.index') }}" class="btn btn-light btn-sm">Back To Presets</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($recommendedAssetKeys->isNotEmpty())
            <div class="col-12">
                <div class="card border-0 shadow-sm {{ $missingRecommendedAssets->isEmpty() ? 'border-success-subtle' : 'border-warning-subtle' }}">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="mb-1">Recommended Family Assets</h5>
                        <p class="text-muted mb-0">These assets are the intended supporting stack for this preset family.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        @if($missingRecommendedAssets->isEmpty())
                            <div class="alert alert-success-subtle border border-success-subtle mb-4">
                                All recommended assets for this preset family are currently enabled.
                            </div>
                        @else
                            <div class="alert alert-warning-subtle border border-warning-subtle mb-4">
                                Some recommended assets are not enabled yet. The preset can still be instantiated, but the family stack is currently incomplete.
                            </div>
                        @endif

                        <div class="row g-3">
                            @foreach($recommendedAssetKeys as $assetKey)
                                @php($asset = $allLibraryItems->firstWhere('key', $assetKey))
                                @php($isEnabled = $enabledRelatedLibraryItems->pluck('key')->contains($assetKey))
                                <div class="col-lg-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                            <div class="fw-semibold">{{ data_get($asset, 'name', $assetKey) }}</div>
                                            <span class="badge {{ $isEnabled ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                                {{ $isEnabled ? 'Enabled' : 'Missing / Disabled' }}
                                            </span>
                                        </div>
                                        <div class="small text-muted mb-2">{{ data_get($asset, 'description', 'No library metadata registered yet for this key.') }}</div>
                                        <div class="small"><span class="text-muted">Key:</span> <code>{{ $assetKey }}</code></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($missingRecommendedAssets->isNotEmpty())
                            <div class="alert alert-light border mt-4 mb-0 small text-muted">
                                To complete this preset family stack, open <a href="{{ route('page-builder.plugins-theme.index', ['preset' => data_get($preset, 'key')]) }}">Plugins / Theme</a> and enable the missing assets listed above.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($presetInstantiation)
            <div class="col-12">
                <div class="card border-0 shadow-sm border-success">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="mb-1">Instantiation Result</h5>
                        <p class="text-muted mb-0">These are the builder records now ready to continue the Start From Template flow.</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-muted mb-1">Core Layout</div>
                                    <div class="fw-semibold">{{ data_get($presetInstantiation, 'core_layout.name', '-') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-muted mb-1">Chrome Layout</div>
                                    <div class="fw-semibold">{{ data_get($presetInstantiation, 'chrome_layout.name', '-') }}</div>
                                </div>
                            </div>
                        </div>

                        @if(!empty(data_get($presetInstantiation, 'enabled_library_items', [])))
                            <div class="border rounded-3 p-3 mb-4">
                        <div class="fw-semibold mb-2">Enabled Family Assets Applied</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(data_get($presetInstantiation, 'enabled_library_items', []) as $asset)
                                        <span class="badge bg-light text-dark">
                                            {{ data_get($asset, 'name') }} · {{ strtoupper(data_get($asset, 'category', 'asset')) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div>
                                <div class="fw-semibold">Starter Draft Pages</div>
                                <p class="text-muted small mb-0">Open the draft directly from here, or continue from the Pages index.</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('page-builder.plugins-theme.index', ['preset' => data_get($preset, 'key')]) }}" class="btn btn-outline-secondary btn-sm">Open Family Assets</a>
                                <a href="{{ route('page-builder.pages.index') }}" class="btn btn-light btn-sm">Open Pages Index</a>
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach(data_get($presetInstantiation, 'pages', []) as $page)
                                <div class="col-lg-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge {{ data_get($page, 'status') === 'created' ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info' }}">
                                                {{ ucfirst(data_get($page, 'status', 'ready')) }}
                                            </span>
                                            <span class="badge bg-light text-dark">{{ data_get($page, 'recipe_key') }}</span>
                                        </div>
                                        <h6 class="mb-1">{{ data_get($page, 'title') }}</h6>
                                        <p class="text-muted small mb-3"><code>/{{ data_get($page, 'slug') }}</code></p>

                                        <div class="d-flex flex-wrap gap-2">
                                            @if(data_get($page, 'edit_url'))
                                                <a href="{{ data_get($page, 'edit_url') }}" class="btn btn-primary btn-sm">Edit Draft</a>
                                            @endif
                                            @if(data_get($page, 'public_url'))
                                                <a href="{{ data_get($page, 'public_url') }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Open Public</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Preset Summary</h5>
                    <p class="text-muted mb-0">This is the Phase F baseline structure for the first internal template family.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted">Key</dt>
                        <dd class="col-7"><code>{{ data_get($preset, 'key') }}</code></dd>

                        <dt class="col-5 text-muted">Category</dt>
                        <dd class="col-7">{{ strtoupper(data_get($preset, 'category', 'preset')) }}</dd>

                        <dt class="col-5 text-muted">Origin</dt>
                        <dd class="col-7">{{ data_get($preset, 'origin.source', '-') }}</dd>

                        <dt class="col-5 text-muted">Core Layout</dt>
                        <dd class="col-7"><code>{{ data_get($preset, 'blueprint.core_layout.key') }}</code></dd>

                        <dt class="col-5 text-muted">Chrome Layout</dt>
                        <dd class="col-7"><code>{{ data_get($preset, 'blueprint.chrome_layout.key') }}</code></dd>

                        <dt class="col-5 text-muted">Library Assets</dt>
                        <dd class="col-7">{{ $relatedLibraryItems->count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Flow Interpretation</h5>
                    <p class="text-muted mb-0">This shows how the current engine is expected to support the first Start From Template experience.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="alert alert-light border mb-3">
                        <div class="fw-semibold mb-1">Instantiation behavior</div>
                        <p class="small text-muted mb-0">
                            The preset upserts its Core Layout and Chrome Layout by stable preset keys, then creates starter draft pages by stable slugs. Existing starter drafts are reused and not overwritten.
                        </p>
                    </div>
                    <div class="vstack gap-3">
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">1. Choose Preset</div>
                            <p class="small text-muted mb-0">User starts from this internal family instead of building header, footer, and theme tokens manually.</p>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">2. Instantiate Shared Engine Records</div>
                            <p class="small text-muted mb-0">The preset should create or reuse compatible Core Layout, Chrome Layout, and starter page records under the builder engine.</p>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold mb-1">3. Edit Safe Controls</div>
                            <p class="small text-muted mb-0">Users should mainly edit content, page settings, SEO, ads awareness, and limited theme overrides instead of every low-level layout field.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Related Library Assets</h5>
                    <p class="text-muted mb-0">These theme/plugin items are operationally linked to this preset family.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    @if($relatedLibraryItems->isEmpty())
                        <div class="text-muted small">No related library assets are registered for this preset yet.</div>
                    @else
                        <div class="vstack gap-3">
                            @foreach($relatedLibraryItems as $item)
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <div class="fw-semibold">{{ data_get($item, 'name') }}</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge {{ data_get($item, 'status') === 'enabled' ? 'bg-success-subtle text-success' : (data_get($item, 'status') === 'disabled' ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning') }}">
                                                {{ ucfirst(data_get($item, 'status', 'planned')) }}
                                            </span>
                                            <span class="badge bg-light text-dark text-uppercase">{{ data_get($item, 'category', 'asset') }}</span>
                                        </div>
                                    </div>
                                    <div class="small text-muted mb-2">{{ data_get($item, 'description') }}</div>
                                    <div class="small">
                                        <span class="text-muted">Key:</span> <code>{{ data_get($item, 'key') }}</code>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="alert alert-light border mt-4 mb-0 small text-muted">
                        Only enabled assets are carried into preset instantiation and recorded into the generated layout/page context.
                        Manage states in <a href="{{ route('page-builder.plugins-theme.index', ['preset' => data_get($preset, 'key')]) }}">Plugins / Theme</a> with this preset filter already applied.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Blueprint</h5>
                    <p class="text-muted mb-0">Current structured data for the preset family.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <pre class="bg-light border rounded-3 p-3 small mb-0" style="white-space: pre-wrap;">{{ json_encode(data_get($preset, 'blueprint', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Notes</h5>
                    <p class="text-muted mb-0">These notes keep the baseline aligned with the current website and future plugin/theme expansion.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="vstack gap-3">
                        @foreach(data_get($preset, 'notes', []) as $note)
                            <div class="border rounded-3 p-3 small text-muted">{{ $note }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
