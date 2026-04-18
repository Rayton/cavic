<li>
    <a href="{{ route('messages.inbox') }}"><i class="fas fa-envelope"></i><span>{{ _lang('Messages') }}</span>{!! ($inbox ?? 0) > 0 ? xss_clean('<div class="circle-animation"></div>') : '' !!}</a>
</li>
