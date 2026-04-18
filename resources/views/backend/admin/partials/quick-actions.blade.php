@php
    $title = $title ?? _lang('Quick Actions');
    $subtitle = $subtitle ?? null;
    $actions = $actions ?? [];
@endphp

@if(! empty($actions))
<div class="card workspace-section-card workspace-quick-actions-card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
            <div class="pr-lg-4 mb-3 mb-lg-0">
                <div class="workspace-section-title mb-1">{{ $title }}</div>
                @if($subtitle)
                    <p class="text-muted small mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="workspace-quick-actions-list d-flex flex-wrap align-items-center">
                @foreach($actions as $action)
                    <a href="{{ $action['url'] ?? '#' }}" class="btn {{ $action['class'] ?? 'btn-outline-primary btn-sm' }} mr-2 mb-2">
                        @if(! empty($action['icon']))
                            <i class="{{ $action['icon'] }} mr-1"></i>
                        @endif
                        {{ $action['label'] ?? _lang('Open') }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
