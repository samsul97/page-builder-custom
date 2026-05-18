@extends('layouts.app')

@section('title', 'Edit Core Layout')

@section('breadcrumbs', Breadcrumbs::render('page-builder.core-layouts.edit', $coreLayout))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Edit Core Layout</h4>
            <p class="text-muted mb-0">Update the design tokens that can be shared by multiple chrome layouts.</p>
        </div>
        <a href="{{ route('page-builder.core-layouts.index') }}" class="btn btn-light">Back to Core Layouts</a>
    </div>

    <form method="POST" action="{{ route('page-builder.core-layouts.update', $coreLayout) }}">
        @csrf
        @method('PUT')
        @include('pagebuilder::core-layouts._form', ['submitLabel' => 'Save Changes'])
    </form>
@endsection
