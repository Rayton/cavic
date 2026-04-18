<div class="message-modal-view">
    @include('messages.partials.details')
    <div class="mt-4">
        <a href="{{ route('messages.reply', $message->uuid) }}" class="btn btn-primary btn-xs"><i class="fas fa-reply mr-2"></i>{{ _lang('Reply') }}</a>
        <a href="{{ route('messages.show', $message->uuid) }}" class="btn btn-outline-primary btn-xs ml-2">{{ _lang('Open Full View') }}</a>
    </div>
</div>
