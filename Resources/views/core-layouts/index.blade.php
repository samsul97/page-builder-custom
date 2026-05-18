@extends('layouts.app')

@section('title', 'Core Layouts')

@section('breadcrumbs', Breadcrumbs::render('page-builder.core-layouts.index'))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Core Layouts</h4>
            <p class="text-muted mb-0">Manage reusable design systems for fonts, colors, spacing, and container rules.</p>
        </div>
        <a href="{{ route('page-builder.core-layouts.create') }}" class="btn btn-success">Create Core Layout</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($coreLayouts->isEmpty())
                <div class="text-center py-5">
                    <h5 class="mb-2">No core layouts yet</h5>
                    <p class="text-muted mb-4">Start by creating a reusable design system before building multiple chrome layouts and pages.</p>
                    <a href="{{ route('page-builder.core-layouts.create') }}" class="btn btn-primary">Create First Core Layout</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Key</th>
                                <th>Type</th>
                                <th>Preset Family</th>
                                <th>Status</th>
                                <th>Chrome Layouts</th>
                                <th>Theme Preview</th>
                                <th>Updated</th>
                                <th class="text-end">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coreLayouts as $coreLayout)
                                @php($settings = \App\Models\PageBuilder\PageBuilderCoreLayout::mergeSettings($coreLayout->settings))
                                @php($presetKey = data_get($coreLayout->settings, 'preset.key'))
                                @php($presetName = data_get($coreLayout->settings, 'preset.name'))
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $coreLayout->name }}</div>
                                        <div class="text-muted small">Font: {{ data_get($settings, 'font_family') }}</div>
                                    </td>
                                    <td><code>{{ $coreLayout->key }}</code></td>
                                    <td>
                                        <span class="badge {{ $presetKey ? 'bg-primary-subtle text-primary' : 'bg-light text-dark' }}">
                                            {{ $presetKey ? 'Preset' : 'Custom' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($presetKey)
                                            <div class="fw-semibold">{{ $presetName ?: $presetKey }}</div>
                                            <a href="{{ route('page-builder.presets.show', $presetKey) }}" class="small">Open Preset</a>
                                        @else
                                            <span class="text-muted small">Custom design system</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $coreLayout->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ $coreLayout->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $coreLayout->chrome_layouts_count }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-block rounded-circle border" style="width: 18px; height: 18px; background: {{ data_get($settings, 'background_color') }};"></span>
                                            <span class="d-inline-block rounded-circle border" style="width: 18px; height: 18px; background: {{ data_get($settings, 'card_color') }};"></span>
                                            <span class="d-inline-block rounded-circle border" style="width: 18px; height: 18px; background: {{ data_get($settings, 'accent_color') }};"></span>
                                            <span class="d-inline-block rounded-circle border" style="width: 18px; height: 18px; background: {{ data_get($settings, 'text_color') }};"></span>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ optional($coreLayout->updated_at)->format('d M Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('page-builder.core-layouts.edit', $coreLayout) }}" class="btn btn-sm btn-info">Edit</a>
                                            <form method="POST" action="{{ route('page-builder.core-layouts.destroy', $coreLayout) }}" onsubmit="return confirm('Delete this core layout?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" {{ $coreLayout->key === 'default' ? 'disabled' : '' }}>Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $coreLayouts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
