@if(page_builder_enabled() && auth()->user()->can('edit-settings'))
<li class="nav-item">
    <a class="nav-link menu-link {{ show_menu_dropdown('page-builder') }}" href="#sidebar-page-builder"
       data-bs-toggle="collapse" role="button"
       aria-expanded="{{ Str::startsWith(Route::currentRouteName(), 'page-builder') ? 'true' : 'false' }}"
       aria-controls="sidebar-page-builder">
        <i class="ri-layout-line"></i> <span>Page Builder</span>
    </a>
    <div class="collapse menu-dropdown {{ show_menu_dropdown('page-builder') }}" id="sidebar-page-builder">
        <ul class="nav nav-sm flex-column">
            <li class="nav-item">
                <a href="{{ route('page-builder.index') }}" class="nav-link {{ set_active('page-builder.index') }}">
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('page-builder.pages.index') }}" class="nav-link {{ set_active('page-builder.pages') }}">
                    Pages
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('page-builder.chrome-layouts.index') }}" class="nav-link {{ set_active('page-builder.chrome-layouts') }}">
                    Layouts
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('page-builder.blocks.index') }}" class="nav-link {{ set_active('page-builder.blocks') }}">
                    Reusable Blocks
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('page-builder.media.index') }}" class="nav-link {{ set_active('page-builder.media') }}">
                    Media
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('page-builder.content-types.index') }}" class="nav-link {{ set_active('page-builder.content-types') }}">
                    Content Types
                </a>
            </li>
        </ul>
    </div>
</li>
@endif
