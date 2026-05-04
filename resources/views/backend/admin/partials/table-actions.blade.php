@php
    $items = $items ?? [];
    $dropdownId = 'table-actions-' . uniqid();
@endphp

@if(! empty($items))
<div class="dropdown table-row-actions">
    <button class="btn btn-xs dropdown-toggle" type="button" id="{{ $dropdownId }}" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-haspopup="true" aria-expanded="false">
        {{ _lang('Action') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $dropdownId }}">
        @foreach($items as $item)
            @if(($item['method'] ?? 'get') === 'delete')
                <form action="{{ $item['url'] ?? '#' }}" method="post" class="{{ $item['form_class'] ?? '' }}">
                    @csrf
                    <input name="_method" type="hidden" value="DELETE">
                    <button class="dropdown-item {{ $item['class'] ?? 'btn-remove' }}" type="submit">
                        @if(! empty($item['icon']))
                            <i class="{{ $item['icon'] }}"></i>
                        @endif
                        <span>{{ $item['label'] ?? _lang('Delete') }}</span>
                    </button>
                </form>
            @else
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="dropdown-item {{ $item['class'] ?? '' }}"
                    @if(! empty($item['data_title'])) data-title="{{ $item['data_title'] }}" @endif
                    @if(array_key_exists('data_fullscreen', $item)) data-fullscreen="{{ $item['data_fullscreen'] ? 'true' : 'false' }}" @endif
                    @if(! empty($item['data_size'])) data-size="{{ $item['data_size'] }}" @endif
                    @if(array_key_exists('data_reload', $item)) data-reload="{{ $item['data_reload'] ? 'true' : 'false' }}" @endif
                    @if(! empty($item['data_confirm'])) data-confirm="{{ $item['data_confirm'] }}" @endif
                    @if(! empty($item['target'])) target="{{ $item['target'] }}" @endif
                >
                    @if(! empty($item['icon']))
                        <i class="{{ $item['icon'] }}"></i>
                    @endif
                    <span>{{ $item['label'] ?? _lang('Open') }}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>
@endif
