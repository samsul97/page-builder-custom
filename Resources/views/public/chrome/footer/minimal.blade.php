<footer class="pb-footer-minimal {{ $footerSurfaceClass }} mt-5">
    <div class="container py-4 py-lg-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <h3 class="h5 fw-bold text-uppercase letter-spacing-1 mb-2">{{ data_get($footer, 'brand_title') }}</h3>
                @if(filled(data_get($footer, 'brand_text')))
                    <p class="small mb-0" style="line-height: 1.8;">{{ data_get($footer, 'brand_text') }}</p>
                @endif
            </div>
            <div class="col-lg-4">
                <div class="d-flex flex-wrap gap-3 justify-content-lg-center">
                    @foreach(data_get($footer, 'journey_links', []) as $link)
                        @if(filled(data_get($link, 'label')) && filled(data_get($link, 'url')))
                            <a class="pb-nav-link" href="{{ $resolveFooterUrl(data_get($link, 'url')) }}">{{ data_get($link, 'label') }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="col-lg-3 text-lg-end">
                @foreach(data_get($footer, 'social_links', []) as $social)
                    @if(filled(data_get($social, 'label')) && filled(data_get($social, 'url')))
                        <a class="pb-nav-link d-inline-block {{ $loop->last ? '' : 'me-3' }}" href="{{ data_get($social, 'url') }}" target="_blank" rel="noopener">{{ data_get($social, 'label') }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    <div class="pb-footer-bottom">
        <div class="container py-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2 small">
                <div>{{ data_get($footer, 'copyright', '© ' . now()->year . ' Rawdee') }}</div>
                <div class="pb-footer-muted">{{ data_get($footer, 'bottom_origin', 'From Rawageude, Bogor - Indonesia') }}</div>
            </div>
        </div>
    </div>
</footer>
