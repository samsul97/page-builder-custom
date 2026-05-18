<footer class="pb-footer {{ $footerSurfaceClass }} mt-5">
    <div class="container py-5 py-lg-6">
        <div class="row g-4 g-xl-5">
            <div class="col-12 col-md-6 col-xl-3">
                <h3 class="h5 fw-bold text-uppercase letter-spacing-1 mb-3">{{ data_get($footer, 'brand_title') }}</h3>
                <p class="small mb-4" style="line-height: 1.8;">{{ data_get($footer, 'brand_text') }}</p>
                <div>
                    <div class="small text-uppercase fw-semibold mb-2 pb-footer-muted" style="letter-spacing: 0.08em;">{{ data_get($footer, 'social_title') }}</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach(data_get($footer, 'social_links', []) as $social)
                            @if(filled(data_get($social, 'label')) && filled(data_get($social, 'url')))
                                <a class="pb-nav-link" href="{{ data_get($social, 'url') }}" target="_blank" rel="noopener">{{ data_get($social, 'label') }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <h3 class="h6 fw-semibold text-uppercase mb-3 pb-footer-muted" style="letter-spacing: 0.08em;">{{ data_get($footer, 'journey_title') }}</h3>
                <div class="d-flex flex-column gap-2">
                    @foreach(data_get($footer, 'journey_links', []) as $link)
                        @if(filled(data_get($link, 'label')) && filled(data_get($link, 'url')))
                            <a class="pb-nav-link" href="{{ $resolveFooterUrl(data_get($link, 'url')) }}">{{ data_get($link, 'label') }}</a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <h3 class="h6 fw-semibold text-uppercase mb-3 pb-footer-muted" style="letter-spacing: 0.08em;">{{ data_get($footer, 'contact_title') }}</h3>
                <div class="d-flex flex-column gap-3">
                    @foreach($footerContacts as $contact)
                        <div>
                            <div class="small fw-semibold mb-1 pb-footer-muted">{{ data_get($contact, 'label') }}</div>
                            <a class="pb-nav-link" href="{{ data_get($contact, 'wa_url') }}" target="_blank" rel="noopener">{{ data_get($contact, 'phone') }}</a>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <h3 class="h6 fw-semibold text-uppercase mb-3 pb-footer-muted" style="letter-spacing: 0.08em;">{{ data_get($footer, 'location_title') }}</h3>
                @foreach(data_get($footer, 'locations', []) as $locationIndex => $location)
                    <div class="{{ $locationIndex > 0 ? 'mt-4' : 'mb-4' }}">
                        @if(filled(data_get($location, 'label')))
                            <a href="{{ $resolveFooterUrl(data_get($location, 'map_url')) }}" class="pb-nav-link d-inline-block mb-2" target="_blank" rel="noopener">{{ data_get($location, 'label') }}</a>
                        @endif
                        <div class="small pb-footer-muted" style="line-height: 1.8;">
                            @foreach(data_get($location, 'lines', []) as $line)
                                @if(filled($line))
                                    <div>{{ $line }}</div>
                                @endif
                            @endforeach
                        </div>
                        @if($locationIndex === 0 && (filled(data_get($location, 'weekday_value')) || filled(data_get($location, 'weekend_value'))))
                            <div class="mt-3">
                                <div class="small fw-semibold mb-1 pb-footer-muted">{{ data_get($footer, 'hours_title') }}</div>
                                @if(filled(data_get($location, 'weekday_value')))
                                    <div class="small pb-footer-muted">{{ data_get($location, 'weekday_label') }} {{ data_get($location, 'weekday_value') }}</div>
                                @endif
                                @if(filled(data_get($location, 'weekend_value')))
                                    <div class="small pb-footer-muted">{{ data_get($location, 'weekend_label') }} {{ data_get($location, 'weekend_value') }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
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
