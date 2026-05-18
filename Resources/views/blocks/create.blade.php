@extends('layouts.app')

@section('title', 'Create Reusable Block')

@section('breadcrumbs', Breadcrumbs::render('page-builder.blocks.create'))

@section('content')
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1">Create Reusable Block</h4>
            <p class="text-muted mb-0">Build a reusable section package that editors can insert into multiple pages.</p>
        </div>
        <a href="{{ route('page-builder.blocks.index') }}" class="btn btn-light">Back to Blocks</a>
    </div>

    <form method="POST" action="{{ route('page-builder.blocks.store') }}">
        @csrf
        @include('pagebuilder::blocks._form', ['submitLabel' => 'Create Reusable Block'])
    </form>
@endsection

@push('vendor-scripts')
    @vite('resources/js/page-builder/editor.jsx')
@endpush
