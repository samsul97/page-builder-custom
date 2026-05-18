<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Http\Requests\StorePageBuilderReusableBlockRequest;
use Modules\PageBuilder\Http\Requests\UpdatePageBuilderReusableBlockRequest;
use Modules\PageBuilder\Models\PageBuilderReusableBlock;
use Modules\PageBuilder\Support\PageBuilderBlockTypeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageBuilderReusableBlockController extends Controller
{
    public function __construct(
        protected PageBuilderBlockTypeRegistry $blockTypeRegistry,
    ) {
    }

    public function index(): View
    {
        return view('pagebuilder::blocks.index', [
            'reusableBlocks' => PageBuilderReusableBlock::query()->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('pagebuilder::blocks.create', [
            'reusableBlock' => new PageBuilderReusableBlock([
                'is_active' => true,
                'blocks' => [],
            ]),
            'blockTypes' => $this->blockTypeRegistry->enabled(),
            'disabledBlockTypesUsed' => [],
        ]);
    }

    public function store(StorePageBuilderReusableBlockRequest $request): RedirectResponse
    {
        $reusableBlock = PageBuilderReusableBlock::create($request->reusableBlockPayload());

        flash()->success('Reusable block created successfully.');

        return redirect()->route('page-builder.blocks.edit', $reusableBlock);
    }

    public function edit(PageBuilderReusableBlock $pageBuilderReusableBlock): View
    {
        return view('pagebuilder::blocks.edit', [
            'reusableBlock' => $pageBuilderReusableBlock,
            'blockTypes' => $this->blockTypeRegistry->enabled(),
            'disabledBlockTypesUsed' => $this->blockTypeRegistry->usedDisabledTypes($pageBuilderReusableBlock->blocks ?? []),
        ]);
    }

    public function update(UpdatePageBuilderReusableBlockRequest $request, PageBuilderReusableBlock $pageBuilderReusableBlock): RedirectResponse
    {
        $pageBuilderReusableBlock->update($request->reusableBlockPayload());

        flash()->success('Reusable block updated successfully.');

        return redirect()->route('page-builder.blocks.edit', $pageBuilderReusableBlock);
    }

    public function destroy(PageBuilderReusableBlock $pageBuilderReusableBlock): RedirectResponse
    {
        $pageBuilderReusableBlock->delete();

        flash()->success('Reusable block deleted successfully.');

        return redirect()->route('page-builder.blocks.index');
    }
}
