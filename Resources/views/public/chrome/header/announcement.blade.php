@if(data_get($navigation, 'show_top_bar') && (filled(data_get($navigation, 'top_bar_text')) || filled(data_get($navigation, 'meta_value')) || filled(data_get($navigation, 'top_bar_link_label'))))
    <div class="pb-announcement">
        <div class="container py-2">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 {{ ($centered ?? false) ? 'small text-center text-lg-start' : 'small' }}">
                <div class="pb-announcement-text">{{ data_get($navigation, 'top_bar_text') }}</div>
                <div class="d-flex flex-wrap align-items-center {{ ($centered ?? false) ? 'justify-content-center justify-content-lg-end' : '' }} gap-3">
                    @if(filled(data_get($navigation, 'meta_value')))
                        <a href="{{ $resolveFooterUrl(data_get($navigation, 'meta_url')) }}" class="pb-announcement-link">
                            @if(filled(data_get($navigation, 'meta_label')))
                                <strong>{{ data_get($navigation, 'meta_label') }}:</strong>
                            @endif
                            {{ data_get($navigation, 'meta_value') }}
                        </a>
                    @endif
                    @if(filled(data_get($navigation, 'top_bar_link_label')))
                        <a href="{{ $resolveFooterUrl(data_get($navigation, 'top_bar_link_url')) }}" class="pb-announcement-link">
                            {{ data_get($navigation, 'top_bar_link_label') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
