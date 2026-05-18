@extends('layouts.app')

@section('title', 'Create Content Type')

@section('breadcrumbs', Breadcrumbs::render('page-builder.content-types.create'))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Create Content Type</h4>
            <p class="text-muted mb-0">Set up a structured collection for future dynamic builder blocks.</p>
        </div>
        <a href="{{ route('page-builder.content-types.index') }}" class="btn btn-light">Back to Content Types</a>
    </div>

    <form method="POST" action="{{ route('page-builder.content-types.store') }}">
        @csrf
        @include('pagebuilder::content-types._form', ['submitLabel' => 'Create Content Type'])
    </form>
@endsection
