@extends('layouts.app')

@section('title', 'Media Library')

@section('breadcrumbs', Breadcrumbs::render('page-builder.media.index'))

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Media Library</h4>
            <p class="text-muted mb-0">Shared media storage for Page Builder images and future asset pickers.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('page-builder.media.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="q" class="form-label">Search</label>
                    <input type="text" id="q" name="q" value="{{ $query }}" class="form-control" placeholder="Search media name or mime type">
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-light">Search</button>
                        <a href="{{ route('page-builder.media.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            <form method="POST" action="{{ route('page-builder.media.store') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label for="file" class="form-label">Upload Image</label>
                    <input type="file" id="file" name="file" accept="image/*" class="form-control @error('file') is-invalid @enderror" required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Upload to Media Library</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($media->isEmpty())
                <div class="text-center py-5">
                    <h5 class="mb-2">No media files yet</h5>
                    <p class="text-muted mb-0">Upload the first image so it can be reused across Page Builder.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($media as $item)
                        <div class="col-md-6 col-xl-3">
                            <div class="card h-100 border shadow-sm">
                                <div class="ratio ratio-4x3 bg-light rounded-top overflow-hidden">
                                    @if($item->isImage())
                                        <img src="{{ $item->url() }}" alt="{{ $item->original_name }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center text-muted">No preview</div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="fw-semibold text-truncate" title="{{ $item->original_name }}">{{ $item->original_name }}</div>
                                    <div class="small text-muted text-truncate" title="{{ $item->path }}">{{ $item->path }}</div>
                                    <div class="small text-muted mt-2">{{ number_format($item->size / 1024, 1) }} KB</div>
                                    @php($usedBy = $item->usedBy())
                                    <div class="small mt-2">
                                        @if(count($usedBy) > 0)
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Used by {{ count($usedBy) }}</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success-emphasis">Unused</span>
                                        @endif
                                    </div>
                                    @if(count($usedBy) > 0)
                                        <div class="small text-muted mt-2">
                                            {{ collect($usedBy)->pluck('label')->take(3)->implode(', ') }}
                                            @if(count($usedBy) > 3)
                                                +{{ count($usedBy) - 3 }} more
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-transparent border-top-0 d-flex gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-light btn-sm flex-fill"
                                        onclick="navigator.clipboard.writeText(@js($item->url()))"
                                    >
                                        Copy URL
                                    </button>
                                    <form method="POST" action="{{ route('page-builder.media.destroy', $item) }}" onsubmit="return confirm('Delete this media file?')" class="flex-fill">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" {{ count($usedBy) > 0 ? 'disabled' : '' }}>
                                            {{ count($usedBy) > 0 ? 'In Use' : 'Delete' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $media->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
