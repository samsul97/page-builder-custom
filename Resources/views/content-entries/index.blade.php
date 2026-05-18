@extends('layouts.app')

@section('title', $contentType->name . ' Entries')

@section('breadcrumbs', Breadcrumbs::render('page-builder.content-types.entries.index', $contentType))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ $contentType->name }} Entries</h4>
            <p class="text-muted mb-0">{{ $contentType->description ?: 'Manage structured entries for this content type.' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('page-builder.content-types.entries.create', $contentType) }}" class="btn btn-success">Create Entry</a>
            <a href="{{ route('page-builder.content-types.edit', $contentType) }}" class="btn btn-light">Back to Content Type</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($entries->isEmpty())
                <div class="text-center py-5">
                    <h5 class="mb-2">No entries yet</h5>
                    <p class="text-muted mb-4">Create the first entry for this content type.</p>
                    <a href="{{ route('page-builder.content-types.entries.create', $contentType) }}" class="btn btn-primary">Create First Entry</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th class="text-end">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                                <tr>
                                    <td>{{ $entry->title }}</td>
                                    <td><code>{{ $entry->slug }}</code></td>
                                    <td>
                                        <span class="badge {{ $entry->is_published ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ $entry->is_published ? __('messages.published') : __('messages.unpublished') }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ optional($entry->updated_at)->format('d M Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('page-builder.content-types.entries.edit', [$contentType, $entry]) }}" class="btn btn-sm btn-info">Edit</a>
                                            <form method="POST" action="{{ route('page-builder.content-types.entries.destroy', [$contentType, $entry]) }}" onsubmit="return confirm('Delete this entry?')">
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
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
