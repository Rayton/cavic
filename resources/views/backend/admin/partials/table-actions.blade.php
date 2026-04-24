@php
    $items = $items ?? [];
    $dropdownId = 'table-actions-' . uniqid();
@endphp

@if(! empty($items))
<div class="dropdown table-row-actions">
    <button class="btn btn-xs dropdown-toggle" type="button" id="{{ $dropdownId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ _lang('Action') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $dropdownId }}">
        @foreach($items as $item)
            <a
                href="{{ $item['url'] ?? '#' }}"
                class="dropdown-item {{ $item['class'] ?? '' }}"
                @if(! empty($item['data_title'])) data-title="{{ $item['data_title'] }}" @endif
                @if(! empty($item['target'])) target="{{ $item['target'] }}" @endif
            >
                @if(! empty($item['icon']))
                    <i class="{{ $item['icon'] }}"></i>
                @endif
                <span>{{ $item['label'] ?? _lang('Open') }}</span>
            </a>
        @endforeach
    </div>
</div>
@endif
