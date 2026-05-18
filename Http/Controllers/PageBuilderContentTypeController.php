<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Http\Requests\StorePageBuilderContentTypeRequest;
use Modules\PageBuilder\Http\Requests\UpdatePageBuilderContentTypeRequest;
use Modules\PageBuilder\Models\PageBuilderContentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageBuilderContentTypeController extends Controller
{
    public function index(): View
    {
        return view('pagebuilder::content-types.index', [
            'contentTypes' => PageBuilderContentType::query()->withCount('entries')->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('pagebuilder::content-types.create', [
            'contentType' => new PageBuilderContentType([
                'is_active' => true,
                'schema' => [],
            ]),
        ]);
    }

    public function store(StorePageBuilderContentTypeRequest $request): RedirectResponse
    {
        $contentType = PageBuilderContentType::create($request->contentTypePayload());

        flash()->success('Content type created successfully.');

        return redirect()->route('page-builder.content-types.edit', $contentType);
    }

    public function edit(PageBuilderContentType $pageBuilderContentType): View
    {
        return view('pagebuilder::content-types.edit', [
            'contentType' => $pageBuilderContentType,
        ]);
    }

    public function update(UpdatePageBuilderContentTypeRequest $request, PageBuilderContentType $pageBuilderContentType): RedirectResponse
    {
        $pageBuilderContentType->update($request->contentTypePayload());

        flash()->success('Content type updated successfully.');

        return redirect()->route('page-builder.content-types.edit', $pageBuilderContentType);
    }

    public function destroy(PageBuilderContentType $pageBuilderContentType): RedirectResponse
    {
        $pageBuilderContentType->delete();

        flash()->success('Content type deleted successfully.');

        return redirect()->route('page-builder.content-types.index');
    }
}
