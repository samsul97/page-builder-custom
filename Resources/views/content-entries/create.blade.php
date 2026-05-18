@extends('layouts.app')

@section('title', 'Create Content Entry')

@section('breadcrumbs', Breadcrumbs::render('page-builder.content-types.entries.create', $contentType))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Create Entry</h4>
            <p class="text-muted mb-0">Add structured data under {{ $contentType->name }}.</p>
        </div>
        <a href="{{ route('page-builder.content-types.entries.index', $contentType) }}" class="btn btn-light">Back to Entries</a>
    </div>

    <form method="POST" action="{{ route('page-builder.content-types.entries.store', $contentType) }}">
        @csrf
        @include('pagebuilder::content-entries._form', ['submitLabel' => 'Create Entry'])
    </form>
@endsection
