@php
    $pageSettings = method_exists($page, 'mergedSettings')
        ? $page->mergedSettings()
        : \App\Models\PageBuilder\PageBuilderPage::defaultSettings();
    $rawMarkup = (string) data_get($pageSettings, 'raw_markup', '');
    $hasFullDocument = str_contains(strtolower($rawMarkup), '<html')
        || str_contains(strtolower($rawMarkup), '<!doctype');
@endphp

@if(filled($rawMarkup) && $hasFullDocument)
    {!! $rawMarkup !!}
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page->meta_title ?: $page->title }}</title>
        @if($page->meta_description)
            <meta name="description" content="{{ $page->meta_description }}">
        @endif
        @if($page->meta_keywords)
            <meta name="keywords" content="{{ $page->meta_keywords }}">
        @endif
        @if(!empty($isPageBuilderPreview))
            <meta name="robots" content="noindex,nofollow">
        @endif
    </head>
    <body>
        @if(filled($rawMarkup))
            {!! $rawMarkup !!}
        @else
            <main style="font-family: system-ui, sans-serif; padding: 48px;">
                <h1>{{ $page->title }}</h1>
                <p>Standalone raw HTML mode is enabled, but no markup has been added yet.</p>
            </main>
        @endif
    </body>
    </html>
@endif
