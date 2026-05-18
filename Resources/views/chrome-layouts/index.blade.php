@extends('layouts.app')

@section('title', 'Chrome Layouts')

@section('breadcrumbs', Breadcrumbs::render('page-builder.chrome-layouts.index'))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Chrome Layouts</h4>
            <p class="text-muted mb-0">Manage reusable header, navigation, and footer layouts that sit on top of a Core Layout.</p>
        </div>
        <a href="{{ route('page-builder.chrome-layouts.create') }}" class="btn btn-success">Create Chrome Layout</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($chromeLayouts->isEmpty())
                <div class="text-center py-5">
                    <h5 class="mb-2">No chrome layouts yet</h5>
                    <p class="text-muted mb-4">Create the first reusable header/footer/navigation layout for your pages.</p>
                    <a href="{{ route('page-builder.chrome-layouts.create') }}" class="btn btn-primary">Create First Chrome Layout</a>
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
                                <th>Core Layout</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th class="text-end">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chromeLayouts as $layout)
                                @php($settings = \App\Models\PageBuilder\PageBuilderLayout::mergeSettings($layout->settings))
                                @php($presetKey = data_get($layout->settings, 'preset.key'))
                                @php($presetName = data_get($layout->settings, 'preset.name'))
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $layout->name }}</div>
                                        <div class="text-muted small">{{ data_get($settings, 'header.brand_name') ?: 'No header brand set' }}</div>
                                        <div class="text-muted small">
                                            H: {{ \Illuminate\Support\Str::headline(data_get($settings, 'header.variant', 'classic')) }} |
                                            F: {{ \Illuminate\Support\Str::headline(data_get($settings, 'footer.variant', 'columns')) }}
                                        </div>
                                    </td>
                                    <td><code>{{ $layout->key }}</code></td>
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
                                            <span class="text-muted small">Custom chrome</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($layout->coreLayout)
                                            <div>{{ $layout->coreLayout->name }}</div>
                                            <a href="{{ route('page-builder.core-layouts.edit', $layout->coreLayout) }}" class="small">Open Core Layout</a>
                                        @else
                                            <span class="text-muted small">No core layout</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $layout->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ $layout->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ optional($layout->updated_at)->format('d M Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('page-builder.chrome-layouts.edit', $layout) }}" class="btn btn-sm btn-info">Edit</a>
                                            <form method="POST" action="{{ route('page-builder.chrome-layouts.destroy', $layout) }}" onsubmit="return confirm('Delete this chrome layout?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" {{ $layout->key === 'default' ? 'disabled' : '' }}>Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $chromeLayouts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
