<li>
    <a href="{{ route('action_center.index') }}"><i class="fas fa-bolt"></i><span>{{ _lang('Action Center') }} @if(($action_center_total ?? 0) > 0)<span class="sidebar-notification-count">{{ $action_center_total }}</span>@endif</span></a>
</li>
