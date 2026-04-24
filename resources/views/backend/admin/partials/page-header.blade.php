@php
    $title = $title ?? ($page_title ?? _lang('Workspace'));
    $subtitle = $subtitle ?? null;
    $badge = $badge ?? null;
    $actions = $actions ?? [];
    $breadcrumbs = $breadcrumbs ?? [];
@endphp

<div class="card workspace-page-header-card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between">
            <div class="pr-lg-4 mb-3 mb-lg-0">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    <h4 class="mb-0">{{ $title }}</h4>
                    @if($badge)
                        <span class="workspace-pill">{{ $badge }}</span>
                    @endif
                </div>
                @if($subtitle)
                    <p class="text-muted mb-0 workspace-page-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            @if(! empty($actions))
                <div class="workspace-page-actions text-lg-right">
                    @foreach($actions as $action)
                        <a href="{{ $action['url'] ?? '#' }}" class="btn {{ $action['class'] ?? 'btn-outline-primary btn-sm' }} {{ ! $loop->last ? 'mr-2' : '' }} mb-2">
                            {{ $action['label'] ?? _lang('Open') }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
