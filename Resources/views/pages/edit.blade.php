@extends('layouts.app')

@section('title', 'Edit Builder Page')

@section('breadcrumbs', Breadcrumbs::render('page-builder.pages.edit', $page))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <h4 class="mb-0">Edit Builder Page</h4>
                @if(!empty($presetContext))
                    <span class="badge bg-primary-subtle text-primary align-self-center">Template-Aware</span>
                @endif
            </div>
            <p class="text-muted mb-0">
                @if(!empty($presetContext))
                    This draft was instantiated from a preset family and now edits against the shared builder engine.
                @else
                    This page already persists against the future builder schema.
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if(!empty($publicUrl))
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-primary">Open Public Page</a>
            @endif
            <a href="{{ route('page-builder.pages.index') }}" class="btn btn-light">Back to Pages</a>
        </div>
    </div>

    <form method="POST" action="{{ route('page-builder.pages.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('pagebuilder::pages._form', ['submitLabel' => 'Save Changes', 'useVisualBuilder' => true])
    </form>
@endsection

@push('vendor-scripts')
    @vite('resources/js/page-builder/editor.jsx')
@endpush
