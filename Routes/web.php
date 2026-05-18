<?php

use Illuminate\Support\Facades\Route;
use Modules\PageBuilder\Http\Controllers\PageBuilderContentEntryController;
use Modules\PageBuilder\Http\Controllers\PageBuilderContentTypeController;
use Modules\PageBuilder\Http\Controllers\PageBuilderController;
use Modules\PageBuilder\Http\Controllers\PageBuilderCoreLayoutController;
use Modules\PageBuilder\Http\Controllers\PageBuilderLayoutController;
use Modules\PageBuilder\Http\Controllers\PageBuilderLibraryController;
use Modules\PageBuilder\Http\Controllers\PageBuilderMediaController;
use Modules\PageBuilder\Http\Controllers\PageBuilderPageController;
use Modules\PageBuilder\Http\Controllers\PageBuilderPresetController;
use Modules\PageBuilder\Http\Controllers\PageBuilderPublicController;
use Modules\PageBuilder\Http\Controllers\PageBuilderReadinessController;
use Modules\PageBuilder\Http\Controllers\PageBuilderReusableBlockController;

// Public (no auth required)
Route::get('/landing-pages/{pageBuilderPage:slug}', [PageBuilderPublicController::class, 'show'])
    ->middleware('page_builder_enabled')
    ->name('page-builder.public.show');

// Admin routes
Route::middleware(['auth', 'page_builder_enabled'])->prefix('page-builder')->name('page-builder.')->group(function () {
    Route::get('/', [PageBuilderController::class, 'index'])->name('index');
    Route::get('/readiness', PageBuilderReadinessController::class)->name('readiness.index');
    Route::post('/readiness/manual-tests', [PageBuilderReadinessController::class, 'updateManualTest'])->name('readiness.manual-tests.update');

    Route::get('/presets', [PageBuilderPresetController::class, 'index'])->name('presets.index');
    Route::get('/presets/{preset}', [PageBuilderPresetController::class, 'show'])->name('presets.show');
    Route::post('/presets/{preset}/instantiate', [PageBuilderPresetController::class, 'instantiate'])->name('presets.instantiate');

    Route::get('/pages', [PageBuilderPageController::class, 'index'])->name('pages.index');
    Route::get('/pages/create', [PageBuilderPageController::class, 'create'])->name('pages.create');
    Route::match(['GET', 'POST'], '/pages/preview', [PageBuilderPublicController::class, 'preview'])->name('pages.preview');
    Route::post('/pages', [PageBuilderPageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{pageBuilderPage}/edit', [PageBuilderPageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{pageBuilderPage}', [PageBuilderPageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{pageBuilderPage}', [PageBuilderPageController::class, 'destroy'])->name('pages.destroy');

    Route::get('/media', [PageBuilderMediaController::class, 'index'])->name('media.index');
    Route::post('/media', [PageBuilderMediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{pageBuilderMedia}', [PageBuilderMediaController::class, 'destroy'])->name('media.destroy');

    Route::get('/blocks', [PageBuilderReusableBlockController::class, 'index'])->name('blocks.index');
    Route::get('/blocks/create', [PageBuilderReusableBlockController::class, 'create'])->name('blocks.create');
    Route::post('/blocks', [PageBuilderReusableBlockController::class, 'store'])->name('blocks.store');
    Route::get('/blocks/{pageBuilderReusableBlock}/edit', [PageBuilderReusableBlockController::class, 'edit'])->name('blocks.edit');
    Route::put('/blocks/{pageBuilderReusableBlock}', [PageBuilderReusableBlockController::class, 'update'])->name('blocks.update');
    Route::delete('/blocks/{pageBuilderReusableBlock}', [PageBuilderReusableBlockController::class, 'destroy'])->name('blocks.destroy');

    Route::get('/core-layouts', [PageBuilderCoreLayoutController::class, 'index'])->name('core-layouts.index');
    Route::get('/core-layouts/create', [PageBuilderCoreLayoutController::class, 'create'])->name('core-layouts.create');
    Route::match(['GET', 'POST'], '/core-layouts/preview', [PageBuilderCoreLayoutController::class, 'preview'])->name('core-layouts.preview');
    Route::post('/core-layouts', [PageBuilderCoreLayoutController::class, 'store'])->name('core-layouts.store');
    Route::get('/core-layouts/{pageBuilderCoreLayout}/edit', [PageBuilderCoreLayoutController::class, 'edit'])->name('core-layouts.edit');
    Route::put('/core-layouts/{pageBuilderCoreLayout}', [PageBuilderCoreLayoutController::class, 'update'])->name('core-layouts.update');
    Route::delete('/core-layouts/{pageBuilderCoreLayout}', [PageBuilderCoreLayoutController::class, 'destroy'])->name('core-layouts.destroy');

    Route::get('/content-types', [PageBuilderContentTypeController::class, 'index'])->name('content-types.index');
    Route::get('/content-types/create', [PageBuilderContentTypeController::class, 'create'])->name('content-types.create');
    Route::post('/content-types', [PageBuilderContentTypeController::class, 'store'])->name('content-types.store');
    Route::get('/content-types/{pageBuilderContentType}/edit', [PageBuilderContentTypeController::class, 'edit'])->name('content-types.edit');
    Route::put('/content-types/{pageBuilderContentType}', [PageBuilderContentTypeController::class, 'update'])->name('content-types.update');
    Route::delete('/content-types/{pageBuilderContentType}', [PageBuilderContentTypeController::class, 'destroy'])->name('content-types.destroy');
    Route::get('/content-types/{pageBuilderContentType}/entries', [PageBuilderContentEntryController::class, 'index'])->name('content-types.entries.index');
    Route::get('/content-types/{pageBuilderContentType}/entries/create', [PageBuilderContentEntryController::class, 'create'])->name('content-types.entries.create');
    Route::post('/content-types/{pageBuilderContentType}/entries', [PageBuilderContentEntryController::class, 'store'])->name('content-types.entries.store');
    Route::get('/content-types/{pageBuilderContentType}/entries/{pageBuilderContentEntry}/edit', [PageBuilderContentEntryController::class, 'edit'])->name('content-types.entries.edit');
    Route::put('/content-types/{pageBuilderContentType}/entries/{pageBuilderContentEntry}', [PageBuilderContentEntryController::class, 'update'])->name('content-types.entries.update');
    Route::delete('/content-types/{pageBuilderContentType}/entries/{pageBuilderContentEntry}', [PageBuilderContentEntryController::class, 'destroy'])->name('content-types.entries.destroy');

    Route::get('/chrome-layouts', [PageBuilderLayoutController::class, 'index'])->name('chrome-layouts.index');
    Route::get('/chrome-layouts/create', [PageBuilderLayoutController::class, 'create'])->name('chrome-layouts.create');
    Route::match(['GET', 'POST'], '/chrome-layouts/preview', [PageBuilderLayoutController::class, 'preview'])->name('chrome-layouts.preview');
    Route::post('/chrome-layouts', [PageBuilderLayoutController::class, 'store'])->name('chrome-layouts.store');
    Route::get('/chrome-layouts/{pageBuilderLayout}/edit', [PageBuilderLayoutController::class, 'edit'])->name('chrome-layouts.edit');
    Route::put('/chrome-layouts/{pageBuilderLayout}', [PageBuilderLayoutController::class, 'update'])->name('chrome-layouts.update');
    Route::delete('/chrome-layouts/{pageBuilderLayout}', [PageBuilderLayoutController::class, 'destroy'])->name('chrome-layouts.destroy');

    Route::get('/plugins-theme', [PageBuilderLibraryController::class, 'index'])->name('plugins-theme.index');
    Route::post('/plugins-theme', [PageBuilderLibraryController::class, 'store'])->name('plugins-theme.store');
    Route::post('/plugins-theme/import-manifest', [PageBuilderLibraryController::class, 'importManifest'])->name('plugins-theme.import-manifest');
    Route::post('/plugins-theme/block-types/{type}/toggle', [PageBuilderLibraryController::class, 'toggleBlockType'])->name('plugins-theme.block-types.toggle');
    Route::post('/plugins-theme/{item}/activate', [PageBuilderLibraryController::class, 'activate'])->name('plugins-theme.activate');
    Route::post('/plugins-theme/{item}/toggle', [PageBuilderLibraryController::class, 'toggle'])->name('plugins-theme.toggle');
    Route::delete('/plugins-theme/{item}', [PageBuilderLibraryController::class, 'destroy'])->name('plugins-theme.destroy');

    Route::redirect('/layout-editor', '/page-builder/chrome-layouts');
});
