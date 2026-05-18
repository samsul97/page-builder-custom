@foreach(data_get($header, 'links', []) as $link)
    @php
        $itemType = data_get($link, 'type', 'link');
        $itemLabel = data_get($link, 'label');
        $itemUrl = data_get($link, 'url');
        $itemResolvedUrl = filled($itemUrl) ? $resolveFooterUrl($itemUrl) : null;
        $itemChildren = collect(data_get($link, 'children', []))->filter(fn ($child) => filled(data_get($child, 'label')) && filled(data_get($child, 'url')));
        $itemSections = collect(data_get($link, 'sections', []))->filter(function ($section) {
            return filled(data_get($section, 'title')) || collect(data_get($section, 'links', []))->contains(fn ($child) => filled(data_get($child, 'label')) && filled(data_get($child, 'url')));
        });
        $itemId = 'pb-nav-item-' . $loop->index;
        $navLinkClass = 'nav-link pb-nav-link';
        $isCurrent = $isCurrentUrl($itemUrl);

        if (data_get($navigation, 'style', 'inline') === 'pill') {
            $navLinkClass .= ' pb-nav-link-pill';
        }

        if ($isCurrent) {
            $navLinkClass .= ' pb-nav-link-current';
        }
    @endphp

    @continue(!filled($itemLabel))

    @if($itemType === 'button')
        <li class="nav-item d-flex align-items-lg-center">
            <a href="{{ $itemResolvedUrl ?: '#' }}" class="btn btn-sm pb-btn-accent ms-lg-2" @if($isExternalUrl($itemUrl)) target="_blank" rel="noopener" @endif>
                {{ $itemLabel }}
            </a>
        </li>
    @elseif($itemType === 'dropdown' && $itemChildren->isNotEmpty())
        <li class="nav-item dropdown pb-nav-dropdown">
            <a href="#" class="{{ $navLinkClass }} dropdown-toggle" id="{{ $itemId }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                {{ $itemLabel }}
            </a>
            <ul class="dropdown-menu pb-dropdown-menu border-0 shadow" aria-labelledby="{{ $itemId }}">
                @if(filled($itemResolvedUrl))
                    <li>
                        <a href="{{ $itemResolvedUrl }}" class="dropdown-item pb-dropdown-item {{ $isCurrent ? 'pb-dropdown-item-current' : '' }}">
                            {{ $itemLabel }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-2"></li>
                @endif
                @foreach($itemChildren as $child)
                    <li>
                        <a href="{{ $resolveFooterUrl(data_get($child, 'url')) }}" class="dropdown-item pb-dropdown-item {{ $isCurrentUrl(data_get($child, 'url')) ? 'pb-dropdown-item-current' : '' }}" @if($isExternalUrl(data_get($child, 'url'))) target="_blank" rel="noopener" @endif>
                            {{ data_get($child, 'label') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @elseif($itemType === 'megamenu' && $itemSections->isNotEmpty())
        <li class="nav-item dropdown pb-nav-megamenu position-static">
            <a href="#" class="{{ $navLinkClass }} dropdown-toggle" id="{{ $itemId }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                {{ $itemLabel }}
            </a>
            <div class="dropdown-menu pb-megamenu-menu border-0 shadow p-0" aria-labelledby="{{ $itemId }}">
                <div class="row g-0">
                    @if(filled($itemResolvedUrl))
                        <div class="col-12">
                            <div class="p-4 pb-megamenu-section pb-megamenu-section-bordered">
                                <a href="{{ $itemResolvedUrl }}" class="pb-dropdown-item text-decoration-none {{ $isCurrent ? 'pb-dropdown-item-current' : '' }}" @if($isExternalUrl($itemUrl)) target="_blank" rel="noopener" @endif>
                                    {{ $itemLabel }}
                                </a>
                            </div>
                        </div>
                    @endif
                    @foreach($itemSections as $section)
                        <div class="col-lg-4">
                            <div class="p-4 h-100 pb-megamenu-section {{ $loop->last ? '' : 'pb-megamenu-section-bordered' }}">
                                @if(filled(data_get($section, 'title')))
                                    <div class="pb-megamenu-title">{{ data_get($section, 'title') }}</div>
                                @endif
                                <div class="d-flex flex-column gap-2">
                                    @foreach(data_get($section, 'links', []) as $child)
                                        @if(filled(data_get($child, 'label')) && filled(data_get($child, 'url')))
                                            <a href="{{ $resolveFooterUrl(data_get($child, 'url')) }}" class="pb-dropdown-item text-decoration-none {{ $isCurrentUrl(data_get($child, 'url')) ? 'pb-dropdown-item-current' : '' }}" @if($isExternalUrl(data_get($child, 'url'))) target="_blank" rel="noopener" @endif>
                                                {{ data_get($child, 'label') }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </li>
    @else
        @if(filled($itemUrl))
            <li class="nav-item">
                <a href="{{ $itemResolvedUrl }}" class="{{ $navLinkClass }}" @if($isExternalUrl($itemUrl)) target="_blank" rel="noopener" @endif>
                    {{ $itemLabel }}
                </a>
            </li>
        @endif
    @endif
@endforeach
