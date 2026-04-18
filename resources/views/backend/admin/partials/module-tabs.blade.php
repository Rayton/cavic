@php
    $tabs = $tabs ?? [];
    $class = $class ?? '';
    $role = $role ?? 'tablist';
    $variant = $variant ?? 'default';
@endphp

@if(! empty($tabs))
    @if($variant === 'top-strip')
        <div class="admin-dashboard-top-tabs nav {{ $class }}" role="{{ $role }}">
            @foreach($tabs as $tab)
                @php
                    $href = $tab['url'] ?? $tab['target'] ?? '#';
                    $isTabToggle = $tab['toggle'] ?? (is_string($href) && str_starts_with($href, '#'));
                @endphp
                <a
                    class="admin-dashboard-top-tab {{ !empty($tab['active']) ? 'active' : '' }}"
                    href="{{ $href }}"
                    @if($isTabToggle) data-toggle="tab" @endif
                    @if(! empty($tab['id'])) id="{{ $tab['id'] }}" @endif
                >
                    @if(! empty($tab['icon']))
                        <i class="{{ $tab['icon'] }} mr-1"></i>
                    @endif
                    <span>{{ $tab['label'] ?? _lang('Tab') }}</span>
                    @if(isset($tab['badge']) && $tab['badge'] !== null && $tab['badge'] !== '')
                        <span class="workspace-tab-badge">{{ $tab['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @else
<ul class="nav nav-pills workspace-nav workspace-module-tabs {{ $class }}" role="{{ $role }}">
    @foreach($tabs as $tab)
        @php
            $href = $tab['url'] ?? $tab['target'] ?? '#';
            $isTabToggle = $tab['toggle'] ?? (is_string($href) && str_starts_with($href, '#'));
        @endphp
        <li class="nav-item">
            <a
                class="nav-link {{ !empty($tab['active']) ? 'active' : '' }}"
                href="{{ $href }}"
                @if($isTabToggle) data-toggle="tab" @endif
                @if(! empty($tab['id'])) id="{{ $tab['id'] }}" @endif
            >
                @if(! empty($tab['icon']))
                    <i class="{{ $tab['icon'] }} mr-1"></i>
                @endif
                <span>{{ $tab['label'] ?? _lang('Tab') }}</span>
                @if(isset($tab['badge']) && $tab['badge'] !== null && $tab['badge'] !== '')
                    <span class="workspace-tab-badge">{{ $tab['badge'] }}</span>
                @endif
            </a>
        </li>
    @endforeach
</ul>
    @endif
@endif
