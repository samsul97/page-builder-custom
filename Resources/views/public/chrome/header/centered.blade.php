@include('pagebuilder::public.chrome.header.announcement', ['navigation' => $navigation, 'centered' => true])

<header class="pb-topbar pb-topbar-centered {{ $headerSurfaceClass }} {{ $navigationDensityClass }} {{ data_get($navigation, 'is_sticky', true) ? 'sticky-top' : '' }}">
    <div class="container py-3 py-lg-4">
        <div class="d-flex flex-column align-items-center text-center gap-3">
            <a href="{{ url('/') }}" class="pb-brand d-flex align-items-center fw-bold text-uppercase letter-spacing-1 gap-2">
                @if(filled(data_get($header, 'brand_logo_url')))
                    <img src="{{ $resolveAssetUrl(data_get($header, 'brand_logo_url')) }}" alt="{{ data_get($header, 'brand_logo_alt', data_get($header, 'brand_name')) }}" style="height: 52px; width: auto;">
                @endif
                <span>{{ data_get($header, 'brand_name', 'Page Builder') }}</span>
                @if(filled(data_get($header, 'tagline')))
                    <small>{{ data_get($header, 'tagline') }}</small>
                @endif
            </a>

            <nav class="navbar navbar-expand-lg p-0 w-100">
                <button class="navbar-toggler mx-auto" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $navMenuId }}" aria-controls="{{ $navMenuId }}" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-center" id="{{ $navMenuId }}">
                    <ul class="navbar-nav flex-row flex-wrap justify-content-center align-items-center gap-2 gap-lg-3">
                        @include('pagebuilder::public.chrome.header.nav-items', ['header' => $header, 'navigation' => $navigation])
                    </ul>
                </div>
            </nav>

            @if(filled(data_get($header, 'button_label')) && filled(data_get($header, 'button_url')))
                <a href="{{ $resolveFooterUrl(data_get($header, 'button_url')) }}" class="btn pb-btn-accent" @if($isExternalUrl(data_get($header, 'button_url'))) target="_blank" rel="noopener" @endif>
                    {{ data_get($header, 'button_label') }}
                </a>
            @endif
        </div>
    </div>
</header>
