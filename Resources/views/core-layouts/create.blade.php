@extends('layouts.app')

@section('title', 'Create Core Layout')

@section('breadcrumbs', Breadcrumbs::render('page-builder.core-layouts.create'))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Create Core Layout</h4>
            <p class="text-muted mb-0">Create a reusable design system for future chrome layouts and pages.</p>
        </div>
        <a href="{{ route('page-builder.core-layouts.index') }}" class="btn btn-light">Back to Core Layouts</a>
    </div>

    <form method="POST" action="{{ route('page-builder.core-layouts.store') }}">
        @csrf
        @include('pagebuilder::core-layouts._form', ['submitLabel' => 'Create Core Layout'])
    </form>
@endsection
