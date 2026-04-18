@php
    $tabs = $tabs ?? [];
    $class = $class ?? '';
    $role = $role ?? 'tablist';
@endphp

@if(! empty($tabs))
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
