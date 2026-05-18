@extends('layouts.app')

@section('title', 'Create Chrome Layout')

@section('breadcrumbs', Breadcrumbs::render('page-builder.chrome-layouts.create'))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Create Chrome Layout</h4>
            <p class="text-muted mb-0">Create a reusable header, navigation, and footer layout for builder pages.</p>
        </div>
        <a href="{{ route('page-builder.chrome-layouts.index') }}" class="btn btn-light">Back to Chrome Layouts</a>
    </div>

    <form method="POST" action="{{ route('page-builder.chrome-layouts.store') }}">
        @csrf
        @include('pagebuilder::chrome-layouts._form', ['submitLabel' => 'Create Chrome Layout'])
    </form>
@endsection
