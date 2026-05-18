@php
    $blocksJson = old(
        'blocks_json',
        json_encode($reusableBlock->blocks ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    $pageBuilderEditorPayload = json_encode([
        'blocks' => $reusableBlock->blocks ?? [],
        'oldBlocksJson' => old('blocks_json'),
        'uploadUrl' => route('page-builder.media.store'),
        'mediaIndexUrl' => route('page-builder.media.index'),
        'reusableBlocks' => [],
        'blockTypes' => ($blockTypes ?? collect())->map(function ($blockType) {
            return [
                'type' => data_get($blockType, 'type'),
                'label' => data_get($blockType, 'label'),
                'description' => data_get($blockType, 'description'),
                'category' => data_get($blockType, 'category'),
                'status' => data_get($blockType, 'status'),
            ];
        })->values()->all(),
        'disabledBlockTypesUsed' => $disabledBlockTypesUsed ?? [],
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        @if(!empty($disabledBlockTypesUsed ?? []))
            <div class="alert alert-warning border-warning-subtle">
                <div class="fw-semibold mb-1">This reusable block uses disabled block type(s)</div>
                <div class="small">
                    Existing content is preserved, but these block types are not available for new inserts:
                    @foreach($disabledBlockTypesUsed as $disabledBlockType)
                        <code>{{ $disabledBlockType }}</code>@if(!$loop->last), @endif
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Reusable Block Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $reusableBlock->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $reusableBlock->slug) }}"
                        class="form-control @error('slug') is-invalid @enderror"
                        placeholder="hero-package-section"
                        required
                    >
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label for="description" class="form-label">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        class="form-control @error('description') is-invalid @enderror"
                    >{{ old('description', $reusableBlock->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <input type="hidden" id="blocks_json" name="blocks_json" value="{{ $blocksJson }}">

        <div
            id="page-builder-editor"
            data-page-builder-editor='{{ $pageBuilderEditorPayload }}'
        ></div>

        @error('blocks_json')
            <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $reusableBlock->is_active) ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="is_active">
                        {{ old('is_active', $reusableBlock->is_active) ? 'Active' : 'Inactive' }}
                    </label>
                </div>

                <div class="text-muted small mb-4">
                    Only active reusable blocks are offered inside the page editor insert panel.
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">
                        {{ $submitLabel }}
                    </button>

                    <a href="{{ route('page-builder.blocks.index') }}" class="btn btn-light">
                        {{ __('messages.cancel') }}
                    </a>
                </div>
            </div>
        </div>

        @if($reusableBlock->exists)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Record Info</h5>
                </div>
                <div class="card-body small text-muted">
                    <div class="mb-2"><strong>ID:</strong> {{ $reusableBlock->id }}</div>
                    <div class="mb-2"><strong>Created:</strong> {{ optional($reusableBlock->created_at)->format('d M Y H:i') ?: '-' }}</div>
                    <div><strong>Updated:</strong> {{ optional($reusableBlock->updated_at)->format('d M Y H:i') ?: '-' }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
