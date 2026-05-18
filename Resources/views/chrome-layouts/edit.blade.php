@extends('layouts.app')

@section('title', 'Edit Chrome Layout')

@section('breadcrumbs', Breadcrumbs::render('page-builder.chrome-layouts.edit', $layout))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Edit Chrome Layout</h4>
            <p class="text-muted mb-0">Manage the header, navigation, and footer content for this layout record.</p>
        </div>
        <a href="{{ route('page-builder.chrome-layouts.index') }}" class="btn btn-light">Back to Chrome Layouts</a>
    </div>

    <form method="POST" action="{{ route('page-builder.chrome-layouts.update', $layout) }}">
        @csrf
        @method('PUT')
        @include('pagebuilder::chrome-layouts._form', ['submitLabel' => 'Save Changes'])
    </form>
@endsection
