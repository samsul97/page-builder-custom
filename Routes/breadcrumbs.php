<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Dashboard > Page Builder
Breadcrumbs::for('page-builder.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Page Builder', route('page-builder.index'));
});

// Dashboard > Page Builder > Pages
Breadcrumbs::for('page-builder.pages.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Pages', route('page-builder.pages.index'));
});

Breadcrumbs::for('page-builder.pages.create', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.pages.index');
    $trail->push('Create');
});

Breadcrumbs::for('page-builder.pages.edit', function (BreadcrumbTrail $trail, $page) {
    $trail->parent('page-builder.pages.index');
    $trail->push($page->title ?? 'Edit');
});

// Dashboard > Page Builder > Reusable Blocks
Breadcrumbs::for('page-builder.blocks.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Reusable Blocks', route('page-builder.blocks.index'));
});

Breadcrumbs::for('page-builder.blocks.create', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.blocks.index');
    $trail->push('Create');
});

Breadcrumbs::for('page-builder.blocks.edit', function (BreadcrumbTrail $trail, $block) {
    $trail->parent('page-builder.blocks.index');
    $trail->push($block->title ?? 'Edit');
});

// Dashboard > Page Builder > Core Layouts
Breadcrumbs::for('page-builder.core-layouts.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Core Layouts', route('page-builder.core-layouts.index'));
});

Breadcrumbs::for('page-builder.core-layouts.create', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.core-layouts.index');
    $trail->push('Create');
});

Breadcrumbs::for('page-builder.core-layouts.edit', function (BreadcrumbTrail $trail, $layout) {
    $trail->parent('page-builder.core-layouts.index');
    $trail->push($layout->name ?? 'Edit');
});

// Dashboard > Page Builder > Chrome Layouts
Breadcrumbs::for('page-builder.chrome-layouts.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Chrome Layouts', route('page-builder.chrome-layouts.index'));
});

Breadcrumbs::for('page-builder.chrome-layouts.create', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.chrome-layouts.index');
    $trail->push('Create');
});

Breadcrumbs::for('page-builder.chrome-layouts.edit', function (BreadcrumbTrail $trail, $layout) {
    $trail->parent('page-builder.chrome-layouts.index');
    $trail->push($layout->name ?? 'Edit');
});

// Dashboard > Page Builder > Content Types
Breadcrumbs::for('page-builder.content-types.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Content Types', route('page-builder.content-types.index'));
});

Breadcrumbs::for('page-builder.content-types.create', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.content-types.index');
    $trail->push('Create');
});

Breadcrumbs::for('page-builder.content-types.edit', function (BreadcrumbTrail $trail, $contentType) {
    $trail->parent('page-builder.content-types.index');
    $trail->push($contentType->name ?? 'Edit');
});

// Dashboard > Page Builder > Content Types > Entries
Breadcrumbs::for('page-builder.content-types.entries.index', function (BreadcrumbTrail $trail, $contentType) {
    $trail->parent('page-builder.content-types.index');
    $trail->push($contentType->name ?? 'Content Type', route('page-builder.content-types.edit', $contentType));
    $trail->push('Entries');
});

Breadcrumbs::for('page-builder.content-types.entries.create', function (BreadcrumbTrail $trail, $contentType) {
    $trail->parent('page-builder.content-types.entries.index', $contentType);
    $trail->push('Create');
});

Breadcrumbs::for('page-builder.content-types.entries.edit', function (BreadcrumbTrail $trail, $contentType, $entry) {
    $trail->parent('page-builder.content-types.entries.index', $contentType);
    $trail->push($entry->title ?? 'Edit');
});

// Dashboard > Page Builder > Media
Breadcrumbs::for('page-builder.media.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Media', route('page-builder.media.index'));
});

// Dashboard > Page Builder > Presets
Breadcrumbs::for('page-builder.presets.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Presets', route('page-builder.presets.index'));
});

Breadcrumbs::for('page-builder.presets.show', function (BreadcrumbTrail $trail, $presetKey) {
    $trail->parent('page-builder.presets.index');
    $trail->push($presetKey ?? 'Preset');
});

// Dashboard > Page Builder > Library
Breadcrumbs::for('page-builder.plugins-theme.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Library', route('page-builder.plugins-theme.index'));
});

// Dashboard > Page Builder > Readiness
Breadcrumbs::for('page-builder.readiness.index', function (BreadcrumbTrail $trail) {
    $trail->parent('page-builder.index');
    $trail->push('Readiness', route('page-builder.readiness.index'));
});

// Dashboard > Settings > Page Builder
Breadcrumbs::for('page-builder.module-settings.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Page Builder', route('page-builder.module-settings.index'));
});
