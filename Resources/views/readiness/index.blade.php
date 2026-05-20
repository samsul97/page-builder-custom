@extends('layouts.app')

@section('title', 'Page Builder Readiness')

@section('breadcrumbs', Breadcrumbs::render('page-builder.readiness.index'))

@php
    $readyCount = collect($checklist)->where('ready', true)->count();
    $totalCount = count($checklist);
    $overallReady = $readyCount === $totalCount;
    $manualStatusBadgeMap = [
        'pass' => 'bg-success-subtle text-success',
        'fail' => 'bg-danger-subtle text-danger',
        'pending' => 'bg-secondary-subtle text-secondary',
    ];
@endphp

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                        <div>
                            <span class="badge bg-dark-subtle text-dark text-uppercase mb-3">Phase J</span>
                            <h4 class="mb-2">Page Builder Readiness</h4>
                            <p class="text-muted mb-0">Operational checklist for preview routes, ads-builder tracking, preset assets, and builder records before client usage.</p>
                        </div>
                        <div class="alert {{ $overallReady ? 'alert-success' : 'alert-warning' }} mb-0 py-3 px-3">
                            <div class="fw-semibold mb-1">{{ $readyCount }} / {{ $totalCount }} checks ready</div>
                            <p class="small mb-0">Use this page as the manual preflight surface before testing landing pages with real tracking or client content.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                        <div>
                            <h5 class="mb-1">Manual Testing Workflow</h5>
                            <p class="text-muted mb-0">Use these focused checks before handing builder pages to client content or real campaign traffic.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-success-subtle text-success">Pass: {{ data_get($manualTestSummary, 'passed', 0) }}</span>
                            <span class="badge bg-danger-subtle text-danger">Fail: {{ data_get($manualTestSummary, 'failed', 0) }}</span>
                            <span class="badge bg-secondary-subtle text-secondary">Pending: {{ data_get($manualTestSummary, 'pending', 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        @foreach($manualTests as $test)
                            <div class="col-lg-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                        <div>
                                            <span class="badge bg-light text-dark mb-2">{{ data_get($test, 'area') }}</span>
                                            <div class="fw-semibold">{{ data_get($test, 'name') }}</div>
                                        </div>
                                        <span class="badge {{ $manualStatusBadgeMap[data_get($test, 'status', 'pending')] ?? $manualStatusBadgeMap['pending'] }}">
                                            {{ ucfirst(data_get($test, 'status', 'pending')) }}
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-3">{{ data_get($test, 'description') }}</p>
                                    @if(data_get($test, 'checked_at'))
                                        <div class="small text-muted mb-2">Last checked: {{ data_get($test, 'checked_at') }}</div>
                                    @endif
                                    @if(data_get($test, 'notes'))
                                        <div class="alert alert-light border py-2 px-3 small mb-3">{{ data_get($test, 'notes') }}</div>
                                    @endif
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <a href="{{ data_get($test, 'url') }}" class="btn btn-sm btn-outline-secondary">Open Surface</a>
                                    </div>
                                    <form action="{{ route('page-builder.readiness.manual-tests.update') }}" method="POST" class="vstack gap-2">
                                        @csrf
                                        <input type="hidden" name="test_key" value="{{ data_get($test, 'key') }}">
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="pending" @selected(data_get($test, 'status') === 'pending')>Pending</option>
                                                    <option value="pass" @selected(data_get($test, 'status') === 'pass')>Pass</option>
                                                    <option value="fail" @selected(data_get($test, 'status') === 'fail')>Fail</option>
                                                </select>
                                            </div>
                                            <div class="col-md-7">
                                                <input type="text" name="notes" value="{{ data_get($test, 'notes') }}" class="form-control form-control-sm" placeholder="Optional note">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-light align-self-start">Update Status</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Readiness Checklist</h5>
                    <p class="text-muted mb-0">These checks reflect the current Page Builder rollout path.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="vstack gap-3">
                        @foreach($checklist as $item)
                            <div class="border rounded-3 p-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge {{ data_get($item, 'ready') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                                {{ data_get($item, 'ready') ? 'Ready' : 'Needs Review' }}
                                            </span>
                                            <div class="fw-semibold">{{ data_get($item, 'label') }}</div>
                                        </div>
                                        <div class="small text-muted">{{ data_get($item, 'detail') }}</div>
                                    </div>
                                    @if(data_get($item, 'url'))
                                        <a href="{{ data_get($item, 'url') }}" class="btn btn-sm btn-light">Open</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Route Surface</h5>
                    <p class="text-muted mb-0">Preview and operational routes expected by the current builder workflow.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Surface</th>
                                    <th>Route Name</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($routeChecks as $routeCheck)
                                    <tr>
                                        <td>{{ data_get($routeCheck, 'label') }}</td>
                                        <td><code>{{ data_get($routeCheck, 'route') }}</code></td>
                                        <td>
                                            <span class="badge {{ data_get($routeCheck, 'ready') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                {{ data_get($routeCheck, 'ready') ? 'Registered' : 'Missing' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if(data_get($routeCheck, 'url'))
                                                <a href="{{ data_get($routeCheck, 'url') }}" class="btn btn-sm btn-outline-secondary">Open</a>
                                            @else
                                                <span class="text-muted small">Internal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Builder Records</h5>
                    <p class="text-muted mb-0">Current content and layout inventory.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        @foreach($counts as $label => $value)
                            <div class="col-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="small text-muted mb-1">{{ str($label)->replace('_', ' ')->title() }}</div>
                                    <div class="fw-semibold">{{ $value }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Ads Builder State</h5>
                    <p class="text-muted mb-0">Landing page tracking uses this scope, separated from website general ads.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="vstack gap-3">
                        @foreach($ads as $label => $ready)
                            <div class="d-flex justify-content-between align-items-center gap-3 border rounded-3 p-3">
                                <div class="fw-semibold small">{{ $label }}</div>
                                <span class="badge {{ $ready ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $ready ? 'Filled' : 'Empty' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if(Route::has('site-settings.ads-builder.edit'))
                    <a href="{{ route('site-settings.ads-builder.edit') }}" class="btn btn-outline-primary btn-sm mt-3">Open Ads Builder</a>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="mb-1">Preset Asset Issues</h5>
                    <p class="text-muted mb-0">Recommended assets should be enabled before template-driven testing.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    @if($recommendedAssetIssues->isEmpty())
                        <div class="alert alert-success mb-0">All recommended preset assets are enabled.</div>
                    @else
                        <div class="vstack gap-3">
                            @foreach($recommendedAssetIssues as $issue)
                                <div class="border rounded-3 p-3">
                                    <div class="fw-semibold mb-1">{{ data_get($issue, 'asset_key') }}</div>
                                    <div class="small text-muted">Preset: {{ data_get($issue, 'preset_name') }}</div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('page-builder.plugins-theme.index') }}" class="btn btn-outline-primary btn-sm mt-3">Open Plugins / Theme</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
