@extends('layouts.app')

@section('title', 'Edit Content Entry')

@section('breadcrumbs', Breadcrumbs::render('page-builder.content-types.entries.edit', $contentType, $entry))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Edit Entry</h4>
            <p class="text-muted mb-0">Update structured data payload for {{ $contentType->name }}.</p>
        </div>
        <a href="{{ route('page-builder.content-types.entries.index', $contentType) }}" class="btn btn-light">Back to Entries</a>
    </div>

    <form method="POST" action="{{ route('page-builder.content-types.entries.update', [$contentType, $entry]) }}">
        @csrf
        @method('PUT')
        @include('pagebuilder::content-entries._form', ['submitLabel' => 'Save Changes'])
    </form>
@endsection
