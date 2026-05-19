@if(auth()->user()->can('edit-settings'))
<li class="nav-item">
    <a href="{{ route('page-builder.module-settings.index') }}"
       class="nav-link {{ set_active('page-builder.module-settings') }}">
        Page Builder
    </a>
</li>
@endif
