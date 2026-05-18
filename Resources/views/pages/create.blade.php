@extends('layouts.app')

@section('title', 'Create Builder Page')

@section('breadcrumbs', Breadcrumbs::render('page-builder.pages.create'))

@section('content')
    <div class="mb-4">
        <h4 class="mb-1">Create Builder Page</h4>
        <p class="text-muted mb-0">Create a new builder page with page details, SEO settings, and the visual block editor.</p>
    </div>

    <form method="POST" action="{{ route('page-builder.pages.store') }}" enctype="multipart/form-data">
        @csrf
        @include('pagebuilder::pages._form', ['submitLabel' => 'Create Page', 'useVisualBuilder' => true])
    </form>
@endsection

@push('vendor-scripts')
    @vite('resources/js/page-builder/editor.jsx')
@endpush
