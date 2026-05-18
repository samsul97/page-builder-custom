@extends('layouts.app')

@section('title', 'Page Builder Presets')

@section('breadcrumbs', Breadcrumbs::render('page-builder.presets.index'))

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                        <div>
                            <span class="badge bg-dark-subtle text-dark text-uppercase mb-3">Phase F</span>
                            <h4 class="mb-2">Preset Catalog</h4>
                            <p class="text-muted mb-0">
                                This catalog defines the first internal <code>Start From Template</code> family before import flow and plugin/theme management are expanded.
                            </p>
                        </div>
                        <div class="alert alert-light border mb-0 py-3 px-3">
                            <div class="fw-semibold mb-1">Current rule</div>
                            <p class="small text-muted mb-0">Presets sit on top of the existing builder engine. They do not replace Core Layouts, Chrome Layouts, or Pages.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach($presets as $preset)
            <div class="col-xl-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-primary-subtle text-primary">{{ ucfirst(data_get($preset, 'status', 'draft')) }}</span>
                                    <span class="badge bg-light text-dark">{{ strtoupper(data_get($preset, 'category', 'preset')) }}</span>
                                </div>
                                <h5 class="mb-2">{{ data_get($preset, 'name') }}</h5>
                                <p class="text-muted mb-0">{{ data_get($preset, 'description') }}</p>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-muted mb-1">Theme Family</div>
                                    <div class="fw-semibold">{{ data_get($preset, 'family.theme_name', '-') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-muted mb-1">Starter Pages</div>
                                    <div class="fw-semibold">{{ data_get($preset, 'starter_page_count', 0) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-muted mb-1">Future Controls</div>
                                    <div class="fw-semibold">{{ data_get($preset, 'future_control_count', 0) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3 bg-light-subtle mb-3">
                            <div class="fw-semibold mb-1">Origin</div>
                            <p class="small text-muted mb-1">{{ data_get($preset, 'origin.note') }}</p>
                            <div class="small">
                                <span class="text-muted">Source:</span> <code>{{ data_get($preset, 'origin.source') }}</code>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <div class="small text-muted">
                                Key: <code>{{ data_get($preset, 'key') }}</code>
                            </div>
                            <a href="{{ route('page-builder.presets.show', data_get($preset, 'key')) }}" class="btn btn-outline-primary btn-sm">
                                View Blueprint
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
