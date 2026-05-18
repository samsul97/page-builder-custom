@include('pagebuilder::public.chrome.header.announcement', ['navigation' => $navigation, 'centered' => false])

<nav class="navbar navbar-expand-lg navbar-light {{ data_get($navigation, 'is_sticky', true) ? 'sticky-top' : '' }} pb-topbar {{ $headerSurfaceClass }} {{ $navigationDensityClass }}">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center fw-bold text-uppercase letter-spacing-1 gap-2 pb-brand" href="{{ url('/') }}">
            @if(filled(data_get($header, 'brand_logo_url')))
                <img src="{{ $resolveAssetUrl(data_get($header, 'brand_logo_url')) }}" alt="{{ data_get($header, 'brand_logo_alt', data_get($header, 'brand_name')) }}" style="height: 44px; width: auto;">
            @endif
            <span>{{ data_get($header, 'brand_name', 'Page Builder') }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $navMenuId }}" aria-controls="{{ $navMenuId }}" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="{{ $navMenuId }}">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                @include('pagebuilder::public.chrome.header.nav-items', ['header' => $header, 'navigation' => $navigation])
            </ul>
            @if(filled(data_get($header, 'button_label')) && filled(data_get($header, 'button_url')))
                <a href="{{ $resolveFooterUrl(data_get($header, 'button_url')) }}" class="btn pb-btn-accent ms-lg-3 mt-3 mt-lg-0" @if($isExternalUrl(data_get($header, 'button_url'))) target="_blank" rel="noopener" @endif>
                    {{ data_get($header, 'button_label') }}
                </a>
            @endif
        </div>
    </div>
</nav>
