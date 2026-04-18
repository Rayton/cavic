<li>
    <a href="{{ route('finance.index') }}"><i class="fas fa-wallet"></i><span>{{ _lang('Finance') }} @if(($finance_queue_total ?? 0) > 0)<span class="sidebar-notification-count">{{ $finance_queue_total }}</span>@endif</span></a>
</li>
