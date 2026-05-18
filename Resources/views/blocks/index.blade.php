@extends('layouts.app')

@section('title', __('messages.blocks'))

@section('breadcrumbs', Breadcrumbs::render('page-builder.blocks.index'))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ __('messages.blocks') }}</h4>
            <p class="text-muted mb-0">Reusable sections that can be inserted into multiple Page Builder pages.</p>
        </div>
        <a href="{{ route('page-builder.blocks.create') }}" class="btn btn-success">
            <i class="ri-add-line align-bottom me-1"></i>
            Create Reusable Block
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($reusableBlocks->isEmpty())
                <div class="text-center py-5">
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title rounded-circle bg-light text-primary fs-2">
                            <i class="ri-layout-grid-line"></i>
                        </div>
                    </div>
                    <h5 class="mb-2">No reusable blocks yet</h5>
                    <p class="text-muted mb-4">Create shared sections here so editors can drop them into landing pages quickly.</p>
                    <a href="{{ route('page-builder.blocks.create') }}" class="btn btn-primary">Create First Reusable Block</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Blocks</th>
                                <th>Updated</th>
                                <th class="text-end">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reusableBlocks as $reusableBlock)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $reusableBlock->name }}</div>
                                        <div class="text-muted small">{{ $reusableBlock->description ?: 'No description yet' }}</div>
                                    </td>
                                    <td><code>{{ $reusableBlock->slug }}</code></td>
                                    <td>
                                        <span class="badge {{ $reusableBlock->is_active ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ $reusableBlock->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ is_array($reusableBlock->blocks) ? count($reusableBlock->blocks) : 0 }}</td>
                                    <td class="text-muted">{{ optional($reusableBlock->updated_at)->format('d M Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('page-builder.blocks.edit', $reusableBlock) }}" class="btn btn-sm btn-info">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('page-builder.blocks.destroy', $reusableBlock) }}" onsubmit="return confirm('Delete this reusable block?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $reusableBlocks->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
