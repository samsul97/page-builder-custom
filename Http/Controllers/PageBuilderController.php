<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;

class PageBuilderController extends Controller
{
    /**
     * @var array<string, array<string, string>>
     */
    protected array $sections = [
        'dashboard' => [
            'title' => 'Page Builder',
            'description' => 'Shared workspace for the builder engine, the future Start From Template flow, and the later theme/plugin library.',
            'route' => 'page-builder.index',
        ],
        'pages' => [
            'title' => 'Pages',
            'description' => 'Manage builder-powered pages, publishing, SEO, and builder-specific ads context.',
            'route' => 'page-builder.pages.index',
        ],
        'presets' => [
            'title' => 'Presets',
            'description' => 'Internal Start From Template catalog for baseline theme families and starter page recipes.',
            'route' => 'page-builder.presets.index',
        ],
        'blocks' => [
            'title' => 'Blocks',
            'description' => 'Save and reuse common sections that both custom and future template flows can consume.',
            'route' => 'page-builder.blocks.index',
        ],
        'core-layouts' => [
            'title' => 'Core Layouts',
            'description' => 'Low-level design systems for fonts, colors, spacing, and visual tokens behind presets.',
            'route' => 'page-builder.core-layouts.index',
        ],
        'content-types' => [
            'title' => 'Content Types',
            'description' => 'Future dynamic content models that should stay shared between custom pages and template families.',
            'route' => 'page-builder.content-types.index',
        ],
        'chrome-layouts' => [
            'title' => 'Chrome Layouts',
            'description' => 'Low-level header, navigation, and footer records for the custom path and future presets.',
            'route' => 'page-builder.chrome-layouts.index',
        ],
        'plugins-theme' => [
            'title' => 'Plugins / Theme',
            'description' => 'Planned categorized library for internal themes and plugin packs with enable, disable, and import states.',
            'route' => 'page-builder.plugins-theme.index',
        ],
    ];

    public function index(): View
    {
        return $this->renderSection('dashboard');
    }

    public function pages(): View
    {
        return $this->renderSection('pages');
    }

    public function presets(): View
    {
        return $this->renderSection('presets');
    }

    public function blocks(): View
    {
        return $this->renderSection('blocks');
    }

    public function contentTypes(): View
    {
        return $this->renderSection('content-types');
    }

    public function coreLayouts(): View
    {
        return $this->renderSection('core-layouts');
    }

    public function chromeLayouts(): View
    {
        return $this->renderSection('chrome-layouts');
    }

    public function pluginsTheme(): View
    {
        return $this->renderSection('plugins-theme');
    }

    protected function renderSection(string $section): View
    {
        abort_unless(isset($this->sections[$section]), 404);

        return view('pagebuilder::index', [
            'pageBuilder' => [
                'activeSection' => $section,
                'current' => Arr::get($this->sections, $section),
                'sections' => array_map(function (string $key, array $meta) {
                    return [
                        'key' => $key,
                        ...$meta,
                        'url' => route($meta['route']),
                    ];
                }, array_keys($this->sections), $this->sections),
            ],
        ]);
    }
}
