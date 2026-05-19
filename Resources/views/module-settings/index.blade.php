@extends('layouts.app')

@section('title', 'Page Builder — Module Settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">

            <div class="card">
                <div class="card-header border-bottom-dashed">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ri-layout-line fs-5 text-primary"></i>
                        <h5 class="card-title mb-0">Page Builder</h5>
                    </div>
                    <p class="text-muted mb-0 mt-1 small">Control visibility and access to the Page Builder module.</p>
                </div>

                <div class="card-body">
                    <form action="{{ route('page-builder.module-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="d-flex align-items-start justify-content-between py-3 border-bottom">
                            <div>
                                <h6 class="mb-1">Enable Page Builder</h6>
                                <p class="text-muted small mb-0">
                                    When enabled, the Page Builder menu appears in the sidebar and all page builder
                                    routes are accessible. Disable to hide from all users.
                                </p>
                            </div>
                            <div class="form-check form-switch ms-4 mt-1">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="enabled" name="enabled" value="1"
                                       {{ $settings->enabled ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
