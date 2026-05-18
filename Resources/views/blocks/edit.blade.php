@extends('layouts.app')

@section('title', 'Edit Reusable Block')

@section('breadcrumbs', Breadcrumbs::render('page-builder.blocks.edit', $reusableBlock))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Edit Reusable Block</h4>
            <p class="text-muted mb-0">Changes here affect the reusable section source for future inserts.</p>
        </div>
        <a href="{{ route('page-builder.blocks.index') }}" class="btn btn-light">Back to Blocks</a>
    </div>

    <form method="POST" action="{{ route('page-builder.blocks.update', $reusableBlock) }}">
        @csrf
        @method('PUT')
        @include('pagebuilder::blocks._form', ['submitLabel' => 'Save Changes'])
    </form>
@endsection

@push('vendor-scripts')
    @vite('resources/js/page-builder/editor.jsx')
@endpush
