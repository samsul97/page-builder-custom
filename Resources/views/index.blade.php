@extends('layouts.app')

@php
    $title = data_get($pageBuilder, 'current.title', 'Page Builder');
    $activeSection = data_get($pageBuilder, 'activeSection', 'dashboard');
    $breadcrumbMap = [
        'dashboard' => 'page-builder.index',
        'pages' => 'page-builder.pages.index',
        'blocks' => 'page-builder.blocks.index',
        'core-layouts' => 'page-builder.core-layouts.index',
        'content-types' => 'page-builder.content-types.index',
        'chrome-layouts' => 'page-builder.chrome-layouts.index',
        'plugins-theme' => 'page-builder.plugins-theme.index',
    ];
@endphp

@section('title', $title)

@section('breadcrumbs', Breadcrumbs::render($breadcrumbMap[$activeSection] ?? 'page-builder.index'))

@section('content')
    <div id="page-builder-app" data-page-builder='@json($pageBuilder)'></div>
@endsection

@push('vendor-scripts')
    @vite('resources/js/page-builder/app.jsx')
@endpush
