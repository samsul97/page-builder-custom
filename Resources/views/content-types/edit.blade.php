@extends('layouts.app')

@section('title', 'Edit Content Type')

@section('breadcrumbs', Breadcrumbs::render('page-builder.content-types.edit', $contentType))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Edit Content Type</h4>
            <p class="text-muted mb-0">Refine the schema and manage content entries under this type.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('page-builder.content-types.entries.index', $contentType) }}" class="btn btn-primary">Manage Entries</a>
            <a href="{{ route('page-builder.content-types.index') }}" class="btn btn-light">Back to Content Types</a>
        </div>
    </div>

    <form method="POST" action="{{ route('page-builder.content-types.update', $contentType) }}">
        @csrf
        @method('PUT')
        @include('pagebuilder::content-types._form', ['submitLabel' => 'Save Changes'])
    </form>
@endsection
