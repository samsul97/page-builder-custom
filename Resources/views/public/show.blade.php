@extends('layouts.page-builder-front')

@php
    $pageSettings = method_exists($page, 'mergedSettings')
        ? $page->mergedSettings()
        : \App\Models\PageBuilder\PageBuilderPage::defaultSettings();
    $layoutMode = data_get($pageSettings, 'layout_mode', \App\Models\PageBuilder\PageBuilderPage::LAYOUT_MODE_INCLUDE);
    $contentMode = data_get($pageSettings, 'content_mode', \App\Models\PageBuilder\PageBuilderPage::CONTENT_MODE_BUILDER);
    $rawMarkup = data_get($pageSettings, 'raw_markup');
    $showHeader = $layoutMode === \App\Models\PageBuilder\PageBuilderPage::LAYOUT_MODE_INCLUDE
        ? (bool) data_get($pageSettings, 'show_header', true)
        : false;
    $showFooter = $layoutMode === \App\Models\PageBuilder\PageBuilderPage::LAYOUT_MODE_INCLUDE
        ? (bool) data_get($pageSettings, 'show_footer', true)
        : false;
    $layoutSettings = \App\Models\PageBuilder\PageBuilderLayout::mergeSettings(data_get($layout ?? null, 'settings'));
    $coreSettings = \App\Models\PageBuilder\PageBuilderCoreLayout::mergeSettings(data_get($coreLayout ?? null, 'settings'));
    $themeOverrides = array_filter((array) data_get($pageSettings, 'theme_overrides', []), fn ($value) => filled($value));
    $legacyTheme = \App\Models\PageBuilder\PageBuilderLayout::legacyThemeSettings(data_get($layout ?? null, 'settings'));
    $theme = array_merge($legacyTheme, $coreSettings, $themeOverrides);
    $header = data_get($layoutSettings, 'header', []);
    $navigation = data_get($layoutSettings, 'navigation', []);
    $footer = data_get($layoutSettings, 'footer', []);
    $chromeVisual = data_get($layoutSettings, 'chrome_visual', []);
    $headerVariant = data_get($header, 'variant', \App\Models\PageBuilder\PageBuilderLayout::HEADER_VARIANT_CLASSIC);
    $footerVariant = data_get($footer, 'variant', \App\Models\PageBuilder\PageBuilderLayout::FOOTER_VARIANT_COLUMNS);
    $navigationDensity = data_get($navigation, 'density', 'comfortable');
    $headerSurfaceStyle = data_get($chromeVisual, 'header_surface_style', 'glass');
    $footerSurfaceStyle = data_get($footer, 'surface_style', 'dark');
    $headerSurfaceClass = match ($headerSurfaceStyle) {
        'solid' => 'pb-topbar-solid',
        'minimal' => 'pb-topbar-minimal',
        default => 'pb-topbar-glass',
    };
    $navigationDensityClass = match ($navigationDensity) {
        'compact' => 'pb-nav-density-compact',
        'relaxed' => 'pb-nav-density-relaxed',
        default => 'pb-nav-density-comfortable',
    };
    $footerSurfaceClass = match ($footerSurfaceStyle) {
        'soft' => 'pb-footer-soft',
        'light' => 'pb-footer-light',
        default => 'pb-footer-dark',
    };
    $navMenuId = 'pageBuilderNavbar-' . ($page->getKey() ?: 'preview');

    $resolveFooterUrl = function (?string $target): string {
        if (! filled($target)) {
            return '#';
        }

        if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://') || str_starts_with($target, 'mailto:') || str_starts_with($target, 'tel:')) {
            return $target;
        }

        return url($target);
    };

    $resolveAssetUrl = function (?string $target): ?string {
        if (! filled($target)) {
            return null;
        }

        if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
            return $target;
        }

        return uploads_url($target);
    };

    $isExternalUrl = function (?string $target): bool {
        if (! filled($target)) {
            return false;
        }

        return str_starts_with($target, 'http://')
            || str_starts_with($target, 'https://')
            || str_starts_with($target, 'mailto:')
            || str_starts_with($target, 'tel:');
    };

    $isCurrentUrl = function (?string $target) use ($resolveFooterUrl): bool {
        if (! filled($target)) {
            return false;
        }

        $resolved = rtrim($resolveFooterUrl($target), '/');
        $current = rtrim(url()->current(), '/');

        return $resolved === $current;
    };

    $footerContacts = collect(data_get($footer, 'contacts', []))
        ->filter(fn ($item) => filled(data_get($item, 'label')) && filled(data_get($item, 'phone')))
        ->map(function ($item) {
            $digits = preg_replace('/\D+/', '', data_get($item, 'phone', ''));

            if (str_starts_with($digits, '0')) {
                $digits = '62' . substr($digits, 1);
            }

            return $item + ['wa_url' => $digits ? 'https://wa.me/' . $digits : '#'];
        });
@endphp

@section('title', $page->meta_title ?: $page->title)

@push('meta')
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
        <meta property="og:description" content="{{ $page->meta_description }}">
        <meta name="twitter:description" content="{{ $page->meta_description }}">
    @endif
    @if($page->meta_keywords)
        <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif
    <meta property="og:title" content="{{ $page->meta_title ?: $page->title }}">
    <meta name="twitter:title" content="{{ $page->meta_title ?: $page->title }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($page->ogImageUrl())
        <meta property="og:image" content="{{ $page->ogImageUrl() }}">
        <meta name="twitter:image" content="{{ $page->ogImageUrl() }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif
@endpush

@push('styles')
    <style>
        .pb-layout {
            --pb-bg: {{ data_get($theme, 'background_color', '#f7f3ea') }};
            --pb-card: {{ data_get($theme, 'card_color', '#ffffff') }};
            --pb-accent: {{ data_get($theme, 'accent_color', '#c46f35') }};
            --pb-text: {{ data_get($theme, 'text_color', '#17261d') }};
            --pb-muted-text: {{ data_get($theme, 'muted_text_color', '#5c6c63') }};
            --pb-font: {!! json_encode(data_get($theme, 'font_family', '"Plus Jakarta Sans", sans-serif')) !!};
            --pb-heading-font: {!! json_encode(data_get($theme, 'heading_font_family', '"Fraunces", serif')) !!};
            --pb-button-radius: {{ data_get($theme, 'button_radius', '999px') }};
            --pb-container-width: {{ data_get($theme, 'container_width', '1200px') }};
            --pb-section-spacing: {{ data_get($theme, 'section_spacing', '5rem') }};
        }

        .pb-page {
            background: var(--pb-bg);
            color: var(--pb-text);
            font-family: var(--pb-font);
        }

        .pb-page .container {
            max-width: var(--pb-container-width);
        }

        .pb-page h1,
        .pb-page h2,
        .pb-page h3,
        .pb-page h4,
        .pb-page h5,
        .pb-page h6 {
            font-family: var(--pb-heading-font);
            color: var(--pb-text);
        }

        .pb-hero {
            background: linear-gradient(135deg, #103425 0%, #c46f35 100%);
            color: #f7f3ea;
            border-radius: 2rem;
            overflow: hidden;
            position: relative;
        }

        .pb-hero::after {
            content: "";
            position: absolute;
            inset: auto -10% -35% auto;
            width: 280px;
            height: 280px;
            border-radius: 999px;
            background: rgba(247, 243, 234, 0.08);
            filter: blur(10px);
        }

        .pb-section-card {
            background: var(--pb-card);
            border: 1px solid rgba(16, 52, 37, 0.08);
            border-radius: 1.5rem;
            box-shadow: 0 1rem 3rem rgba(16, 52, 37, 0.06);
        }

        .pb-prose {
            white-space: pre-wrap;
            line-height: 1.8;
            color: var(--pb-muted-text);
        }

        .pb-image {
            border-radius: 1.5rem;
            overflow: hidden;
            background: var(--pb-card);
            border: 1px solid rgba(16, 52, 37, 0.08);
            box-shadow: 0 1rem 2.5rem rgba(16, 52, 37, 0.08);
        }

        .pb-image img {
            width: 100%;
            max-height: 560px;
            object-fit: cover;
            display: block;
        }

        .pb-caption {
            font-size: 0.92rem;
            color: var(--pb-muted-text);
        }

        .pb-cta {
            background: linear-gradient(180deg, #ffffff 0%, #f0e3d2 100%);
        }

        .pb-grid-card {
            height: 100%;
            background: var(--pb-card);
            border: 1px solid rgba(16, 52, 37, 0.08);
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 1rem 2.5rem rgba(16, 52, 37, 0.05);
        }

        .pb-gallery-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 1rem;
        }

        .pb-gallery-item {
            grid-column: span 6;
            border-radius: 1.5rem;
            overflow: hidden;
            background: var(--pb-card);
            border: 1px solid rgba(16, 52, 37, 0.08);
            box-shadow: 0 1rem 2.5rem rgba(16, 52, 37, 0.05);
        }

        .pb-gallery-item img {
            display: block;
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .pb-faq-item {
            background: var(--pb-card);
            border: 1px solid rgba(16, 52, 37, 0.08);
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 1rem 2rem rgba(16, 52, 37, 0.05);
        }

        .pb-social-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.85rem 1.15rem;
            border-radius: 999px;
            border: 1px solid rgba(16, 52, 37, 0.12);
            background: var(--pb-card);
            color: var(--pb-text);
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 0.75rem 2rem rgba(16, 52, 37, 0.05);
        }

        .pb-social-chip:hover {
            color: var(--pb-accent);
            border-color: rgba(196, 111, 53, 0.32);
        }

        .pb-video-shell {
            overflow: hidden;
            border-radius: 1.5rem;
            background: #0f172a;
            box-shadow: 0 1rem 2.5rem rgba(16, 52, 37, 0.08);
        }

        .pb-video-shell iframe,
        .pb-video-shell video {
            display: block;
            width: 100%;
            border: 0;
        }

        .pb-slideshow .carousel-item img {
            display: block;
            width: 100%;
            height: 520px;
            object-fit: cover;
        }

        .pb-slideshow-caption {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.08) 0%, rgba(15, 23, 42, 0.72) 100%);
        }

        .pb-photo-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 1rem;
        }

        .pb-photo-grid-item {
            grid-column: span 4;
            border-radius: 1.5rem;
            overflow: hidden;
            background: var(--pb-card);
            border: 1px solid rgba(16, 52, 37, 0.08);
            box-shadow: 0 1rem 2.5rem rgba(16, 52, 37, 0.05);
        }

        .pb-photo-grid-item img {
            display: block;
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .pb-timeline {
            position: relative;
        }

        .pb-timeline::before {
            content: "";
            position: absolute;
            left: 0.65rem;
            top: 0.5rem;
            bottom: 0.5rem;
            width: 2px;
            background: rgba(196, 111, 53, 0.25);
        }

        .pb-timeline-item {
            position: relative;
            padding-left: 2.25rem;
        }

        .pb-timeline-item::before {
            content: "";
            position: absolute;
            left: 0.2rem;
            top: 0.55rem;
            width: 0.95rem;
            height: 0.95rem;
            border-radius: 999px;
            background: var(--pb-accent);
            box-shadow: 0 0 0 0.3rem rgba(196, 111, 53, 0.15);
        }

        .pb-announcement {
            background: #103425;
            color: rgba(247, 243, 234, 0.88);
        }

        .pb-announcement-text,
        .pb-announcement-link {
            color: rgba(247, 243, 234, 0.88);
            text-decoration: none;
        }

        .pb-announcement-link:hover {
            color: #fff;
        }

        .pb-topbar {
            z-index: 30;
            border-bottom: 1px solid rgba(16, 52, 37, 0.06);
        }

        .pb-topbar-glass {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            box-shadow: 0 0.125rem 0.5rem rgba(15, 23, 42, 0.08);
        }

        .pb-topbar-solid {
            background: #ffffff;
            box-shadow: 0 0.5rem 1.5rem rgba(15, 23, 42, 0.06);
        }

        .pb-topbar-minimal {
            background: rgba(255, 255, 255, 0.92);
            box-shadow: none;
            border-bottom-color: rgba(16, 52, 37, 0.04);
        }

        .pb-brand {
            color: var(--pb-text);
            text-decoration: none;
        }

        .pb-topbar .navbar-brand,
        .pb-topbar .navbar-brand:hover,
        .pb-topbar .navbar-brand:focus {
            color: var(--pb-text);
        }

        .pb-topbar-centered .pb-brand {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .pb-topbar-centered .pb-brand small {
            display: block;
        }

        .pb-topbar .navbar-nav {
            --bs-nav-link-padding-x: 0.75rem;
        }

        .pb-nav-density-compact .container {
            padding-top: 0.4rem;
            padding-bottom: 0.4rem;
        }

        .pb-nav-density-compact .navbar-nav {
            --bs-nav-link-padding-x: 0.55rem;
        }

        .pb-nav-density-compact .pb-nav-link-pill {
            padding: 0.5rem 0.8rem !important;
        }

        .pb-nav-density-relaxed .container {
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }

        .pb-nav-density-relaxed .navbar-nav {
            --bs-nav-link-padding-x: 1rem;
        }

        .pb-nav-density-relaxed .pb-nav-link-pill {
            padding: 0.85rem 1.15rem !important;
        }

        .pb-brand small,
        .pb-nav-link,
        .pb-topbar .navbar-nav .nav-link {
            color: var(--pb-muted-text);
            text-decoration: none;
            font-weight: 500;
        }

        .pb-topbar .navbar-nav .nav-link.active,
        .pb-topbar .navbar-nav .nav-link.show,
        .pb-topbar .navbar-nav .nav-link.pb-nav-link-current,
        .pb-dropdown-item:hover,
        .pb-dropdown-item.pb-dropdown-item-current,
        .pb-nav-link:hover,
        .pb-brand:hover {
            color: var(--pb-accent);
        }

        .pb-nav-link-pill {
            padding: 0.7rem 1rem !important;
            border-radius: 999px;
            border: 1px solid rgba(16, 52, 37, 0.12);
            background: rgba(255, 255, 255, 0.8);
        }

        .pb-topbar-pill .navbar-nav {
            gap: 0.35rem;
        }

        .pb-dropdown-menu,
        .pb-megamenu-menu {
            margin-top: 0.85rem;
            border-radius: 1rem;
            border: 1px solid rgba(16, 52, 37, 0.08);
            box-shadow: 0 1rem 2rem rgba(16, 52, 37, 0.08);
        }

        .pb-dropdown-menu {
            min-width: 240px;
            padding: 0.65rem;
        }

        .pb-megamenu-menu {
            left: 50% !important;
            transform: translateX(-50%);
            min-width: min(920px, calc(100vw - 2rem));
            overflow: hidden;
        }

        .pb-megamenu-section {
            background: #fff;
        }

        .pb-megamenu-section-bordered {
            border-right: 1px solid rgba(16, 52, 37, 0.08);
        }

        .pb-megamenu-title {
            margin-bottom: 0.85rem;
            font-weight: 700;
            color: var(--pb-text);
            font-family: var(--pb-heading-font);
        }

        .pb-dropdown-item {
            color: var(--pb-muted-text);
            display: block;
            padding: 0.55rem 0.25rem;
            text-decoration: none;
            border-radius: 0.5rem;
        }

        .pb-dropdown-item:hover {
            background: rgba(196, 111, 53, 0.08);
        }

        .pb-dropdown-item.pb-dropdown-item-current,
        .pb-nav-link.pb-nav-link-current {
            color: var(--pb-accent);
            font-weight: 700;
        }

        .pb-btn-accent {
            background: var(--pb-accent);
            border-color: var(--pb-accent);
            color: #fff;
            border-radius: var(--pb-button-radius);
        }

        .pb-btn-accent:hover,
        .pb-btn-accent:focus {
            background: var(--pb-accent);
            border-color: var(--pb-accent);
            color: #fff;
            opacity: 0.92;
        }

        .pb-footer-dark,
        .pb-footer-minimal.pb-footer-dark {
            background: linear-gradient(180deg, #103425 0%, #0b2419 100%);
            color: #f7f3ea;
        }

        .pb-footer-dark p,
        .pb-footer-dark .pb-nav-link,
        .pb-footer-dark .pb-footer-muted,
        .pb-footer-minimal.pb-footer-dark p,
        .pb-footer-minimal.pb-footer-dark .pb-nav-link,
        .pb-footer-minimal.pb-footer-dark .pb-footer-muted {
            color: rgba(247, 243, 234, 0.78);
        }

        .pb-footer-dark .pb-nav-link:hover,
        .pb-footer-minimal.pb-footer-dark .pb-nav-link:hover {
            color: #f7f3ea;
        }

        .pb-footer-soft,
        .pb-footer-minimal.pb-footer-soft {
            background: linear-gradient(180deg, #f0e3d2 0%, #e5d5c2 100%);
            color: var(--pb-text);
        }

        .pb-footer-soft p,
        .pb-footer-soft .pb-nav-link,
        .pb-footer-soft .pb-footer-muted,
        .pb-footer-minimal.pb-footer-soft p,
        .pb-footer-minimal.pb-footer-soft .pb-nav-link,
        .pb-footer-minimal.pb-footer-soft .pb-footer-muted {
            color: rgba(23, 38, 29, 0.72);
        }

        .pb-footer-soft .pb-nav-link:hover,
        .pb-footer-minimal.pb-footer-soft .pb-nav-link:hover {
            color: var(--pb-accent);
        }

        .pb-footer-light,
        .pb-footer-minimal.pb-footer-light {
            background: #ffffff;
            color: var(--pb-text);
            border-top: 1px solid rgba(16, 52, 37, 0.08);
        }

        .pb-footer-light p,
        .pb-footer-light .pb-nav-link,
        .pb-footer-light .pb-footer-muted,
        .pb-footer-minimal.pb-footer-light p,
        .pb-footer-minimal.pb-footer-light .pb-nav-link,
        .pb-footer-minimal.pb-footer-light .pb-footer-muted {
            color: rgba(23, 38, 29, 0.72);
        }

        .pb-footer-light .pb-nav-link:hover,
        .pb-footer-minimal.pb-footer-light .pb-nav-link:hover {
            color: var(--pb-accent);
        }

        .pb-footer-bottom {
            border-top: 1px solid rgba(247, 243, 234, 0.12);
            background: rgba(0, 0, 0, 0.12);
        }

        .pb-footer-soft .pb-footer-bottom,
        .pb-footer-minimal.pb-footer-soft .pb-footer-bottom,
        .pb-footer-light .pb-footer-bottom,
        .pb-footer-minimal.pb-footer-light .pb-footer-bottom {
            border-top-color: rgba(16, 52, 37, 0.08);
            background: rgba(16, 52, 37, 0.03);
        }

        .pb-spacer {
            width: 100%;
        }

        .pb-dynamic-card {
            height: 100%;
            background: var(--pb-card);
            border: 1px solid rgba(16, 52, 37, 0.08);
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 1rem 2rem rgba(16, 52, 37, 0.05);
        }

        .pb-dynamic-meta {
            font-size: 0.9rem;
            color: var(--pb-muted-text);
        }

        .pb-raw-markup {
            color: var(--pb-text);
        }

        .pb-section-gap {
            margin-bottom: var(--pb-section-spacing);
        }

        @media (max-width: 991.98px) {
            .pb-topbar .navbar-collapse {
                padding-top: 1rem;
            }

            .pb-dropdown-menu,
            .pb-megamenu-menu {
                min-width: 100%;
                margin-top: 0.5rem;
                transform: none;
                left: 0 !important;
                right: auto;
            }

            .pb-megamenu-section-bordered {
                border-right: 0;
                border-bottom: 1px solid rgba(16, 52, 37, 0.08);
            }

            .pb-gallery-item {
                grid-column: span 12;
            }

            .pb-photo-grid-item {
                grid-column: span 6;
            }
        }

        @media (max-width: 767.98px) {
            .pb-photo-grid-item {
                grid-column: span 12;
            }
        }
    </style>
@endpush

@section('content')
    <div class="pb-layout">
        @if($showHeader)
            @includeIf('pagebuilder::public.chrome.header.' . $headerVariant, ['header' => $header, 'navigation' => $navigation, 'navMenuId' => $navMenuId])
        @endif

        <div class="pb-page py-5 py-lg-6">
            @if($contentMode === \App\Models\PageBuilder\PageBuilderPage::CONTENT_MODE_RAW_HTML)
                <div class="container">
                    @if(filled($rawMarkup))
                        <div class="pb-raw-markup">
                            {!! $rawMarkup !!}
                        </div>
                    @else
                        <section class="pb-section-card p-4 p-lg-5">
                            <h1 class="h2 mb-3">{{ $page->title }}</h1>
                            <p class="text-muted mb-0">Raw HTML mode is enabled, but no markup has been added yet.</p>
                        </section>
                    @endif
                </div>
            @else
                <div class="container">
                    @forelse($blocks as $block)
                        @php
                            $type = data_get($block, 'type');
                            $data = data_get($block, 'data', []);
                        @endphp

                        @switch($type)
                            @case('hero')
                                <section class="pb-hero px-4 px-lg-5 py-5 py-lg-6 mb-4 mb-lg-5">
                                    <div class="row align-items-center g-4 position-relative" style="z-index: 1;">
                                        <div class="col-lg-8">
                                            @if(filled(data_get($data, 'eyebrow')))
                                                <div class="text-uppercase small fw-semibold mb-3" style="letter-spacing: 0.18em; color: rgba(247, 243, 234, 0.7);">
                                                    {{ data_get($data, 'eyebrow') }}
                                                </div>
                                            @endif
                                            <h1 class="display-4 fw-bold mb-3">{{ data_get($data, 'title', $page->title) }}</h1>
                                            @if(filled(data_get($data, 'subtitle')))
                                                <p class="lead mb-4" style="max-width: 46rem; color: rgba(247, 243, 234, 0.82);">{{ data_get($data, 'subtitle') }}</p>
                                            @endif
                                            @if(filled(data_get($data, 'button_label')) && filled(data_get($data, 'button_url')))
                                                <a href="{{ data_get($data, 'button_url') }}" class="btn btn-light btn-lg">{{ data_get($data, 'button_label') }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </section>
                                @break

                            @case('text')
                                <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                    @if(filled(data_get($data, 'title')))
                                        <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                    @endif
                                    @if(filled(data_get($data, 'content')))
                                        <div class="pb-prose mb-0">{{ data_get($data, 'content') }}</div>
                                    @endif
                                </section>
                                @break

                        @case('image')
                            <section class="pb-image mb-4 mb-lg-5">
                                    @php
                                        $imageSource = filled(data_get($data, 'path')) ? uploads_url(data_get($data, 'path')) : data_get($data, 'url');
                                    @endphp
                                    @if(filled($imageSource))
                                        <img src="{{ $imageSource }}" alt="{{ data_get($data, 'alt', $page->title) }}">
                                    @else
                                        <div class="py-5 text-center text-muted">Image URL not configured.</div>
                                    @endif
                                    @if(filled(data_get($data, 'caption')))
                                        <div class="px-4 py-3 pb-caption">{{ data_get($data, 'caption') }}</div>
                                    @endif
                            </section>
                            @break

                        @case('video')
                            @php
                                $videoUrl = data_get($data, 'url');
                                $posterUrl = filled(data_get($data, 'poster_path')) ? uploads_url(data_get($data, 'poster_path')) : data_get($data, 'poster_url');
                                $embedUrl = null;

                                if (filled($videoUrl)) {
                                    if (str_contains($videoUrl, 'youtube.com/watch?v=')) {
                                        parse_str(parse_url($videoUrl, PHP_URL_QUERY) ?: '', $youtubeQuery);
                                        $embedUrl = filled($youtubeQuery['v'] ?? null) ? 'https://www.youtube.com/embed/' . $youtubeQuery['v'] : null;
                                    } elseif (str_contains($videoUrl, 'youtu.be/')) {
                                        $youtubeId = trim((string) basename(parse_url($videoUrl, PHP_URL_PATH) ?: ''), '/');
                                        $embedUrl = filled($youtubeId) ? 'https://www.youtube.com/embed/' . $youtubeId : null;
                                    } elseif (str_contains($videoUrl, 'vimeo.com/')) {
                                        $vimeoSegments = array_values(array_filter(explode('/', parse_url($videoUrl, PHP_URL_PATH) ?: '')));
                                        $vimeoId = end($vimeoSegments) ?: null;
                                        $embedUrl = filled($vimeoId) ? 'https://player.vimeo.com/video/' . $vimeoId : null;
                                    }
                                }
                            @endphp
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                <div class="pb-video-shell">
                                    @if(filled($embedUrl))
                                        <div class="ratio ratio-16x9">
                                            <iframe src="{{ $embedUrl }}" title="{{ data_get($data, 'title', 'Video') }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                        </div>
                                    @elseif(filled($videoUrl))
                                        <video controls poster="{{ $posterUrl ?: '' }}">
                                            <source src="{{ $videoUrl }}">
                                        </video>
                                    @else
                                        <div class="ratio ratio-16x9 d-flex align-items-center justify-content-center text-white-50">
                                            Video URL not configured yet.
                                        </div>
                                    @endif
                                </div>
                                @if(filled(data_get($data, 'caption')))
                                    <div class="pb-caption mt-3">{{ data_get($data, 'caption') }}</div>
                                @endif
                            </section>
                            @break

                        @case('video_grid')
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                <div class="row g-3">
                                    @foreach(data_get($data, 'items', []) as $item)
                                        @php
                                            $videoUrl = data_get($item, 'url');
                                            $posterUrl = filled(data_get($item, 'poster_path')) ? uploads_url(data_get($item, 'poster_path')) : data_get($item, 'poster_url');
                                            $embedUrl = null;

                                            if (filled($videoUrl)) {
                                                if (str_contains($videoUrl, 'youtube.com/watch?v=')) {
                                                    parse_str(parse_url($videoUrl, PHP_URL_QUERY) ?: '', $youtubeQuery);
                                                    $embedUrl = filled($youtubeQuery['v'] ?? null) ? 'https://www.youtube.com/embed/' . $youtubeQuery['v'] : null;
                                                } elseif (str_contains($videoUrl, 'youtu.be/')) {
                                                    $youtubeId = trim((string) basename(parse_url($videoUrl, PHP_URL_PATH) ?: ''), '/');
                                                    $embedUrl = filled($youtubeId) ? 'https://www.youtube.com/embed/' . $youtubeId : null;
                                                } elseif (str_contains($videoUrl, 'vimeo.com/')) {
                                                    $vimeoSegments = array_values(array_filter(explode('/', parse_url($videoUrl, PHP_URL_PATH) ?: '')));
                                                    $vimeoId = end($vimeoSegments) ?: null;
                                                    $embedUrl = filled($vimeoId) ? 'https://player.vimeo.com/video/' . $vimeoId : null;
                                                }
                                            }
                                        @endphp
                                        <div class="col-md-6">
                                            <article class="pb-dynamic-card h-100">
                                                <div class="pb-video-shell mb-3">
                                                    @if(filled($embedUrl))
                                                        <div class="ratio ratio-16x9">
                                                            <iframe src="{{ $embedUrl }}" title="{{ data_get($item, 'title', 'Video') }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                        </div>
                                                    @elseif(filled($videoUrl))
                                                        <video controls poster="{{ $posterUrl ?: '' }}">
                                                            <source src="{{ $videoUrl }}">
                                                        </video>
                                                    @else
                                                        <div class="ratio ratio-16x9 d-flex align-items-center justify-content-center text-white-50">
                                                            Video URL not configured.
                                                        </div>
                                                    @endif
                                                </div>
                                                @if(filled(data_get($item, 'title')))
                                                    <h3 class="h5 mb-2">{{ data_get($item, 'title') }}</h3>
                                                @endif
                                                @if(filled(data_get($item, 'description')))
                                                    <div class="pb-prose small">{{ data_get($item, 'description') }}</div>
                                                @endif
                                            </article>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                            @break

                        @case('slideshow')
                            @php
                                $slides = collect(data_get($data, 'slides', []))->filter(fn ($slide) => filled(data_get($slide, 'path')) || filled(data_get($slide, 'url')));
                                $carouselId = 'pb-slideshow-' . $loop->index;
                            @endphp
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                @if($slides->isEmpty())
                                    <div class="alert alert-light border mb-0">No slides configured yet.</div>
                                @else
                                    <div id="{{ $carouselId }}" class="carousel slide pb-slideshow overflow-hidden rounded-4" data-bs-ride="carousel">
                                        <div class="carousel-indicators">
                                            @foreach($slides as $slideIndex => $slide)
                                                <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $slideIndex }}" class="{{ $slideIndex === 0 ? 'active' : '' }}" aria-current="{{ $slideIndex === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $slideIndex + 1 }}"></button>
                                            @endforeach
                                        </div>
                                        <div class="carousel-inner">
                                            @foreach($slides as $slideIndex => $slide)
                                                @php
                                                    $slideImage = filled(data_get($slide, 'path')) ? uploads_url(data_get($slide, 'path')) : data_get($slide, 'url');
                                                @endphp
                                                <div class="carousel-item {{ $slideIndex === 0 ? 'active' : '' }}">
                                                    <img src="{{ $slideImage }}" alt="{{ data_get($slide, 'alt', data_get($slide, 'title', 'Slide image')) }}">
                                                    @if(filled(data_get($slide, 'title')) || filled(data_get($slide, 'description')) || (filled(data_get($slide, 'button_label')) && filled(data_get($slide, 'button_url'))))
                                                        <div class="carousel-caption text-start pb-slideshow-caption rounded-4 p-4 p-lg-5">
                                                            @if(filled(data_get($slide, 'title')))
                                                                <h3 class="h2 text-white mb-2">{{ data_get($slide, 'title') }}</h3>
                                                            @endif
                                                            @if(filled(data_get($slide, 'description')))
                                                                <p class="mb-3 text-white-50">{{ data_get($slide, 'description') }}</p>
                                                            @endif
                                                            @if(filled(data_get($slide, 'button_label')) && filled(data_get($slide, 'button_url')))
                                                                <a href="{{ data_get($slide, 'button_url') }}" class="btn btn-light">{{ data_get($slide, 'button_label') }}</a>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($slides->count() > 1)
                                            <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </section>
                            @break

                        @case('photogrid')
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                <div class="pb-photo-grid">
                                    @foreach(data_get($data, 'items', []) as $item)
                                        @php
                                            $photoGridImage = filled(data_get($item, 'path')) ? uploads_url(data_get($item, 'path')) : data_get($item, 'url');
                                        @endphp
                                        <article class="pb-photo-grid-item">
                                            @if(filled($photoGridImage))
                                                <img src="{{ $photoGridImage }}" alt="{{ data_get($item, 'alt', data_get($item, 'title', 'Photo')) }}">
                                            @else
                                                <div class="py-5 text-center text-muted">Image not configured.</div>
                                            @endif
                                            @if(filled(data_get($item, 'title')) || filled(data_get($item, 'caption')))
                                                <div class="p-4">
                                                    @if(filled(data_get($item, 'title')))
                                                        <h3 class="h5 mb-2">{{ data_get($item, 'title') }}</h3>
                                                    @endif
                                                    @if(filled(data_get($item, 'caption')))
                                                        <div class="pb-caption">{{ data_get($item, 'caption') }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                            @break

                        @case('cta')
                            <section class="pb-section-card pb-cta p-4 p-lg-5 mb-4 mb-lg-5">
                                <div class="row align-items-center g-4">
                                    <div class="col-lg-8">
                                        @if(filled(data_get($data, 'title')))
                                            <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                        @endif
                                        @if(filled(data_get($data, 'content')))
                                            <p class="lead mb-0 text-muted">{{ data_get($data, 'content') }}</p>
                                        @endif
                                    </div>
                                    <div class="col-lg-4 text-lg-end">
                                        @if(filled(data_get($data, 'button_label')) && filled(data_get($data, 'button_url')))
                                            <a href="{{ data_get($data, 'button_url') }}" class="btn btn-accent btn-lg">{{ data_get($data, 'button_label') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </section>
                            @break

                        @case('feature_grid')
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                <div class="row g-3">
                                    @foreach(data_get($data, 'items', []) as $item)
                                        <div class="col-md-6 col-xl-4">
                                            <div class="pb-grid-card">
                                                @if(filled(data_get($item, 'title')))
                                                    <h3 class="h5 mb-2">{{ data_get($item, 'title') }}</h3>
                                                @endif
                                                @if(filled(data_get($item, 'description')))
                                                    <p class="mb-0 text-muted">{{ data_get($item, 'description') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                            @break

                        @case('gallery')
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                <div class="pb-gallery-grid">
                                    @foreach(data_get($data, 'images', []) as $image)
                                        @php
                                            $galleryImageSource = filled(data_get($image, 'path')) ? uploads_url(data_get($image, 'path')) : data_get($image, 'url');
                                        @endphp
                                        <article class="pb-gallery-item">
                                            @if(filled($galleryImageSource))
                                                <img src="{{ $galleryImageSource }}" alt="{{ data_get($image, 'alt', $page->title) }}">
                                            @else
                                                <div class="py-5 text-center text-muted">Image not configured.</div>
                                            @endif
                                            @if(filled(data_get($image, 'caption')))
                                                <div class="px-4 py-3 pb-caption">{{ data_get($image, 'caption') }}</div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                            @break

                        @case('faq')
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                <div class="vstack gap-3">
                                    @foreach(data_get($data, 'items', []) as $item)
                                        <article class="pb-faq-item">
                                            @if(filled(data_get($item, 'question')))
                                                <h3 class="h5 mb-2">{{ data_get($item, 'question') }}</h3>
                                            @endif
                                            @if(filled(data_get($item, 'answer')))
                                                <div class="pb-prose">{{ data_get($item, 'answer') }}</div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                            @break

                        @case('social_media')
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(data_get($data, 'items', []) as $item)
                                        @if(filled(data_get($item, 'url')))
                                            <a href="{{ data_get($item, 'url') }}" class="pb-social-chip" target="_blank" rel="noopener">
                                                {{ data_get($item, 'label') ?: data_get($item, 'platform', 'Social') }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </section>
                            @break

                        @case('timeline')
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif
                                <div class="pb-timeline vstack gap-4">
                                    @foreach(data_get($data, 'items', []) as $item)
                                        <article class="pb-timeline-item">
                                            @if(filled(data_get($item, 'date')))
                                                <div class="small text-muted fw-semibold mb-2">{{ data_get($item, 'date') }}</div>
                                            @endif
                                            @if(filled(data_get($item, 'title')))
                                                <h3 class="h5 mb-2">{{ data_get($item, 'title') }}</h3>
                                            @endif
                                            @if(filled(data_get($item, 'description')))
                                                <div class="pb-prose">{{ data_get($item, 'description') }}</div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                            @break

                        @case('spacer')
                            <div class="pb-spacer" style="height: {{ max(0, (int) data_get($data, 'height', 80)) }}px;"></div>
                            @break

                        @case('dynamic_collection')
                            @php
                                $contentTypeId = (int) data_get($data, 'content_type_id');
                                $limit = max(1, (int) data_get($data, 'limit', 3));
                                $entries = collect(data_get($dynamicEntries ?? collect(), $contentTypeId, []))->take($limit);
                            @endphp
                            <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                @if(filled(data_get($data, 'title')))
                                    <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                @endif
                                @if(filled(data_get($data, 'subtitle')))
                                    <p class="lead text-muted mb-4">{{ data_get($data, 'subtitle') }}</p>
                                @endif

                                @if($entries->isEmpty())
                                    <div class="alert alert-light border mb-0">
                                        No published entries found for this content type yet.
                                    </div>
                                @else
                                    <div class="row g-3">
                                        @foreach($entries as $entry)
                                            @php
                                                $entryData = data_get($entry, 'data', []);
                                                $entryImage = data_get($entryData, 'image_url') ?: data_get($entryData, 'avatar_url');
                                                $entryTitle = data_get($entryData, 'title') ?: data_get($entryData, 'question') ?: data_get($entry, 'title');
                                                $entryBody = data_get($entryData, 'description') ?: data_get($entryData, 'answer') ?: data_get($entryData, 'quote');
                                                $entryMeta = collect([
                                                    data_get($entryData, 'author'),
                                                    data_get($entryData, 'role'),
                                                    data_get($entryData, 'rating') ? 'Rating: ' . data_get($entryData, 'rating') : null,
                                                ])->filter()->implode(' • ');
                                                $entryButtonLabel = data_get($entryData, 'button_label');
                                                $entryButtonUrl = data_get($entryData, 'button_url');
                                                $entryExtraFields = collect($entryData)
                                                    ->except([
                                                        'image_url',
                                                        'avatar_url',
                                                        'title',
                                                        'question',
                                                        'description',
                                                        'answer',
                                                        'quote',
                                                        'author',
                                                        'role',
                                                        'rating',
                                                        'button_label',
                                                        'button_url',
                                                    ])
                                                    ->filter(fn ($value) => filled($value));
                                            @endphp

                                            <div class="col-md-6 col-xl-4">
                                                <article class="pb-dynamic-card">
                                                    @if(filled($entryImage))
                                                        <div class="mb-3 overflow-hidden rounded-4">
                                                            <img src="{{ $entryImage }}" alt="{{ $entryTitle }}" class="img-fluid w-100" style="height: 220px; object-fit: cover;">
                                                        </div>
                                                    @endif

                                                    <h3 class="h5 mb-2">{{ $entryTitle }}</h3>

                                                    @if(filled($entryBody))
                                                        <div class="pb-prose small mb-3">{{ $entryBody }}</div>
                                                    @endif

                                                    @if(filled($entryMeta))
                                                        <div class="pb-dynamic-meta mb-3">{{ $entryMeta }}</div>
                                                    @endif

                                                    @if($entryExtraFields->isNotEmpty())
                                                        <div class="small text-muted mb-3">
                                                            @foreach($entryExtraFields as $extraKey => $extraValue)
                                                                <div class="{{ $loop->last ? '' : 'mb-1' }}">
                                                                    <strong>{{ \Illuminate\Support\Str::headline((string) $extraKey) }}:</strong>
                                                                    @if(is_array($extraValue))
                                                                        {{ json_encode($extraValue, JSON_UNESCAPED_SLASHES) }}
                                                                    @else
                                                                        {{ $extraValue }}
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @if(filled($entryButtonLabel) && filled($entryButtonUrl))
                                                        <a href="{{ $entryButtonUrl }}" class="btn btn-sm pb-btn-accent">
                                                            {{ $entryButtonLabel }}
                                                        </a>
                                                    @endif
                                                </article>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </section>
                            @break

                        @case('form')
                            @php
                                $selectedForm = data_get($forms ?? collect(), (int) data_get($data, 'form_id'));
                            @endphp
                            <section class="pb-section-card pb-cta p-4 p-lg-5 mb-4 mb-lg-5">
                                <div class="row align-items-center g-4">
                                    <div class="col-lg-8">
                                        @if(filled(data_get($data, 'title')))
                                            <h2 class="h1 mb-3">{{ data_get($data, 'title') }}</h2>
                                        @endif
                                        @if(filled(data_get($data, 'content')))
                                            <p class="lead mb-3 text-muted">{{ data_get($data, 'content') }}</p>
                                        @endif
                                        <div class="small text-muted mb-0">
                                            Selected form:
                                            <strong>{{ data_get($selectedForm, 'name', 'Not selected yet') }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 text-lg-end">
                                        @if($selectedForm)
                                            <a href="{{ route('forms.submissions.create', $selectedForm->id) }}" class="btn pb-btn-accent btn-lg">
                                                {{ data_get($data, 'button_label', 'Open Form') }}
                                            </a>
                                        @else
                                            <span class="btn btn-light btn-lg disabled">Select Form First</span>
                                        @endif
                                    </div>
                                </div>
                            </section>
                            @break

                            @default
                                <section class="pb-section-card p-4 p-lg-5 mb-4 mb-lg-5">
                                    <div class="small text-uppercase fw-semibold text-muted mb-3">{{ $type ?: 'unknown' }}</div>
                                    <pre class="mb-0 small">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </section>
                        @endswitch
                    @empty
                        <section class="pb-section-card p-4 p-lg-5">
                            <h1 class="h2 mb-3">{{ $page->title }}</h1>
                            <p class="text-muted mb-0">This builder page has no blocks yet.</p>
                        </section>
                    @endforelse
                </div>
            @endif
        </div>

        @if($showFooter)
            @includeIf('pagebuilder::public.chrome.footer.' . $footerVariant, ['footer' => $footer, 'footerContacts' => $footerContacts])
        @endif
    </div>
@endsection
