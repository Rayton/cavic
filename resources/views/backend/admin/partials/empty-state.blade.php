@php
    $title = $title ?? _lang('Nothing to show yet');
    $description = $description ?? _lang('There are no records to display right now.');
    $icon = $icon ?? 'ti-layout-grid2';
    $actions = $actions ?? [];
    $class = $class ?? '';
@endphp

<div class="workspace-empty-state {{ $class }}">
    <div class="workspace-empty-state-icon">
        <i class="{{ $icon }}"></i>
    </div>
    <h6 class="workspace-empty-state-title mb-2">{{ $title }}</h6>
    <p class="workspace-empty-state-text mb-3">{{ $description }}</p>

    @if(! empty($actions))
        <div class="workspace-empty-state-actions d-flex flex-wrap justify-content-center">
            @foreach($actions as $action)
                <a href="{{ $action['url'] ?? '#' }}" class="btn {{ $action['class'] ?? 'btn-outline-primary btn-sm' }} mr-2 mb-2">
                    {{ $action['label'] ?? _lang('Open') }}
                </a>
            @endforeach
        </div>
    @endif
</div>
