@extends('layouts.app')

@section('title', __('messages.pages'))

@section('breadcrumbs', Breadcrumbs::render('page-builder.pages.index'))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ __('messages.pages') }}</h4>
            <p class="text-muted mb-0">Builder-backed pages now live in isolated `pb_pages` storage.</p>
        </div>
        <a href="{{ route('page-builder.pages.create') }}" class="btn btn-success">
            <i class="ri-add-line align-bottom me-1"></i>
            Create Page
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($pages->isEmpty())
                <div class="text-center py-5">
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title rounded-circle bg-light text-primary fs-2">
                            <i class="ri-file-list-3-line"></i>
                        </div>
                    </div>
                    <h5 class="mb-2">No builder pages yet</h5>
                    <p class="text-muted mb-4">Create the first page so the visual builder can be plugged into real data next.</p>
                    <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary">Create First Page</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.title') }}</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Layout</th>
                                <th>Content</th>
                                <th>Blocks</th>
                                <th>Updated</th>
                                <th class="text-end">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pages as $page)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $page->title }}</div>
                                        <div class="text-muted small">{{ $page->meta_title ?: 'No meta title yet' }}</div>
                                        @php($settings = method_exists($page, 'mergedSettings') ? $page->mergedSettings() : [])
                                        @if(data_get($settings, 'preset.key'))
                                            <div class="small mt-1">
                                                <span class="badge bg-primary-subtle text-primary">
                                                    Preset: {{ data_get($settings, 'preset.name', data_get($settings, 'preset.key')) }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($page->meta_keywords)
                                            <div class="text-muted small">Keywords: {{ \Illuminate\Support\Str::limit($page->meta_keywords, 80) }}</div>
                                        @endif
                                    </td>
                                    <td><code>/{{ $page->slug }}</code></td>
                                    <td>
                                        <span class="badge {{ $page->is_published ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ $page->is_published ? __('messages.published') : __('messages.unpublished') }}
                                        </span>
                                        @if($page->is_published)
                                            <div class="small mt-1">
                                                <a href="{{ route('page-builder.public.show', $page) }}" target="_blank" rel="noopener">Open public page</a>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-capitalize">{{ data_get($settings, 'layout_mode', 'include') }}</div>
                                        <div class="text-muted small">
                                            H: {{ data_get($settings, 'show_header', true) ? 'On' : 'Off' }} |
                                            F: {{ data_get($settings, 'show_footer', true) ? 'On' : 'Off' }}
                                        </div>
                                        <div class="text-muted small">
                                            Core: {{ optional(($coreLayouts ?? collect())->firstWhere('id', data_get($settings, 'core_layout_id')))->name ?: 'Default' }}
                                        </div>
                                        <div class="text-muted small">
                                            Chrome: {{ optional(($chromeLayouts ?? collect())->firstWhere('id', data_get($settings, 'chrome_layout_id')))->name ?: 'Default' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark text-capitalize">
                                            {{ str_replace('_', ' ', data_get($settings, 'content_mode', 'builder')) }}
                                        </span>
                                    </td>
                                    <td>{{ is_array($page->blocks) ? count($page->blocks) : 0 }}</td>
                                    <td class="text-muted">{{ optional($page->updated_at)->format('d M Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('page-builder.pages.edit', $page) }}" class="btn btn-sm btn-info">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('page-builder.pages.destroy', $page) }}" onsubmit="return confirm('Delete this builder page?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pages->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
