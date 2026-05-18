<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Http\Requests\StorePageBuilderContentEntryRequest;
use Modules\PageBuilder\Http\Requests\UpdatePageBuilderContentEntryRequest;
use Modules\PageBuilder\Models\PageBuilderContentEntry;
use Modules\PageBuilder\Models\PageBuilderContentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageBuilderContentEntryController extends Controller
{
    public function index(PageBuilderContentType $pageBuilderContentType): View
    {
        return view('pagebuilder::content-entries.index', [
            'contentType' => $pageBuilderContentType,
            'entries' => $pageBuilderContentType->entries()->latest()->paginate(12),
        ]);
    }

    public function create(PageBuilderContentType $pageBuilderContentType): View
    {
        return view('pagebuilder::content-entries.create', [
            'contentType' => $pageBuilderContentType,
            'entry' => new PageBuilderContentEntry([
                'is_published' => true,
                'data' => [],
            ]),
        ]);
    }

    public function store(StorePageBuilderContentEntryRequest $request, PageBuilderContentType $pageBuilderContentType): RedirectResponse
    {
        $entry = $pageBuilderContentType->entries()->create($request->contentEntryPayload());

        flash()->success('Content entry created successfully.');

        return redirect()->route('page-builder.content-types.entries.edit', [$pageBuilderContentType, $entry]);
    }

    public function edit(PageBuilderContentType $pageBuilderContentType, PageBuilderContentEntry $pageBuilderContentEntry): View
    {
        abort_unless($pageBuilderContentEntry->content_type_id === $pageBuilderContentType->id, 404);

        return view('pagebuilder::content-entries.edit', [
            'contentType' => $pageBuilderContentType,
            'entry' => $pageBuilderContentEntry,
        ]);
    }

    public function update(
        UpdatePageBuilderContentEntryRequest $request,
        PageBuilderContentType $pageBuilderContentType,
        PageBuilderContentEntry $pageBuilderContentEntry
    ): RedirectResponse {
        abort_unless($pageBuilderContentEntry->content_type_id === $pageBuilderContentType->id, 404);

        $pageBuilderContentEntry->update($request->contentEntryPayload());

        flash()->success('Content entry updated successfully.');

        return redirect()->route('page-builder.content-types.entries.edit', [$pageBuilderContentType, $pageBuilderContentEntry]);
    }

    public function destroy(PageBuilderContentType $pageBuilderContentType, PageBuilderContentEntry $pageBuilderContentEntry): RedirectResponse
    {
        abort_unless($pageBuilderContentEntry->content_type_id === $pageBuilderContentType->id, 404);

        $pageBuilderContentEntry->delete();

        flash()->success('Content entry deleted successfully.');

        return redirect()->route('page-builder.content-types.entries.index', $pageBuilderContentType);
    }
}
