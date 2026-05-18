@extends('layouts.app')

@section('title', __('messages.content_types'))

@section('breadcrumbs', Breadcrumbs::render('page-builder.content-types.index'))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ __('messages.content_types') }}</h4>
            <p class="text-muted mb-0">Define lightweight structured collections for future dynamic page builder blocks.</p>
        </div>
        <a href="{{ route('page-builder.content-types.create') }}" class="btn btn-success">Create Content Type</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($contentTypes->isEmpty())
                <div class="text-center py-5">
                    <h5 class="mb-2">No content types yet</h5>
                    <p class="text-muted mb-4">Start with collections like testimonials, FAQ items, promos, packages, or gallery items.</p>
                    <a href="{{ route('page-builder.content-types.create') }}" class="btn btn-primary">Create First Content Type</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Entries</th>
                                <th>Updated</th>
                                <th class="text-end">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contentTypes as $contentType)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $contentType->name }}</div>
                                        <div class="text-muted small">{{ $contentType->description ?: 'No description yet' }}</div>
                                    </td>
                                    <td><code>{{ $contentType->slug }}</code></td>
                                    <td>
                                        <span class="badge {{ $contentType->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ $contentType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $contentType->entries_count }}</td>
                                    <td class="text-muted">{{ optional($contentType->updated_at)->format('d M Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('page-builder.content-types.entries.index', $contentType) }}" class="btn btn-sm btn-primary">Entries</a>
                                            <a href="{{ route('page-builder.content-types.edit', $contentType) }}" class="btn btn-sm btn-info">Edit</a>
                                            <form method="POST" action="{{ route('page-builder.content-types.destroy', $contentType) }}" onsubmit="return confirm('Delete this content type?')">
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
                    {{ $contentTypes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
