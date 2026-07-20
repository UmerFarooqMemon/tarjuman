@php
    $hasChildren = ! empty($item['children']);
    $isActive = ! empty($item['active']);
    $panelId = 'dockPanel_'.$item['id'].'_'.$context;
@endphp

@if ($context === 'desktop')
<li
    class="admin-dock__item {{ $isActive ? 'is-active' : '' }}"
    data-dock-item
    data-dock-label="{{ $item['label'] }}"
    data-priority="{{ $item['priority'] ?? 99 }}">
    @if ($hasChildren)
        <button
            type="button"
            class="admin-dock__btn"
            data-dock-toggle="{{ $item['id'] }}"
            data-dock-tip="{{ $item['label'] }}"
            aria-haspopup="true"
            aria-expanded="false"
            aria-controls="{{ $panelId }}"
            aria-label="{{ $item['label'] }}">
            <span class="admin-dock__icon"><i class="{{ $item['icon'] }}"></i></span>
            <span class="admin-dock__label">{{ $item['label'] }}</span>
        </button>
        <div class="admin-dock__dropdown" id="{{ $panelId }}" data-dock-panel="{{ $item['id'] }}" hidden role="menu">
            @foreach ($item['children'] as $child)
                @if (($child['action'] ?? null) === 'set-layout')
                    <button
                        type="button"
                        class="admin-dock__dropdown-item"
                        role="menuitem"
                        data-set-admin-nav-layout="{{ $child['layout'] }}">
                        @if (! empty($child['icon']))<i class="{{ $child['icon'] }} me-2"></i>@endif
                        {{ $child['label'] }}
                    </button>
                @else
                    <a
                        class="admin-dock__dropdown-item {{ ! empty($child['active']) ? 'is-active' : '' }}"
                        role="menuitem"
                        href="{{ $child['url'] }}">
                        @if (! empty($child['icon']))<i class="{{ $child['icon'] }} me-2"></i>@endif
                        {{ $child['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    @else
        <a
            class="admin-dock__btn"
            href="{{ $item['url'] }}"
            data-dock-tip="{{ $item['label'] }}"
            aria-label="{{ $item['label'] }}"
            @if ($isActive) aria-current="page" @endif>
            <span class="admin-dock__icon"><i class="{{ $item['icon'] }}"></i></span>
            <span class="admin-dock__label">{{ $item['label'] }}</span>
        </a>
    @endif
</li>
@else
    {{-- Mobile iPhone-style grid tiles --}}
    <li class="admin-dock-drawer__item {{ $isActive ? 'is-active' : '' }}">
        @if ($hasChildren)
            <button
                type="button"
                class="admin-dock-drawer__tile"
                data-dock-mobile-folder
                aria-expanded="false"
                aria-label="{{ $item['label'] }}">
                <span class="admin-dock-drawer__tile-icon"><i class="{{ $item['icon'] }}"></i></span>
                <span class="admin-dock-drawer__tile-label">{{ $item['label'] }}</span>
            </button>
            <div class="admin-dock-drawer__folder" hidden data-dock-folder-panel>
                <div class="admin-dock-drawer__folder-head">
                    <button type="button" class="admin-dock-drawer__folder-back btn btn-sm" data-dock-mobile-folder-back aria-label="{{ __('general.cancel') }}">
                        <i class="ti ti-chevron-left"></i>
                    </button>
                    <span class="admin-dock-drawer__folder-title">{{ $item['label'] }}</span>
                </div>
                <ul class="admin-dock-drawer__grid list-unstyled mb-0">
                    @foreach ($item['children'] as $child)
                        <li class="admin-dock-drawer__item {{ ! empty($child['active']) ? 'is-active' : '' }}">
                            @if (($child['action'] ?? null) === 'set-layout')
                                <button
                                    type="button"
                                    class="admin-dock-drawer__tile"
                                    data-set-admin-nav-layout="{{ $child['layout'] }}">
                                    <span class="admin-dock-drawer__tile-icon">
                                        <i class="{{ $child['icon'] ?? 'ti ti-layout' }}"></i>
                                    </span>
                                    <span class="admin-dock-drawer__tile-label">{{ $child['label'] }}</span>
                                </button>
                            @else
                                <a
                                    class="admin-dock-drawer__tile {{ ! empty($child['active']) ? 'is-active' : '' }}"
                                    href="{{ $child['url'] }}"
                                    data-dock-close-drawer>
                                    <span class="admin-dock-drawer__tile-icon">
                                        <i class="{{ $child['icon'] ?? 'ti ti-point' }}"></i>
                                    </span>
                                    <span class="admin-dock-drawer__tile-label">{{ $child['label'] }}</span>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <a
                class="admin-dock-drawer__tile"
                href="{{ $item['url'] }}"
                data-dock-close-drawer
                @if ($isActive) aria-current="page" @endif>
                <span class="admin-dock-drawer__tile-icon"><i class="{{ $item['icon'] }}"></i></span>
                <span class="admin-dock-drawer__tile-label">{{ $item['label'] }}</span>
            </a>
        @endif
    </li>
@endif
