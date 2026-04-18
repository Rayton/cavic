@extends('layouts.app')

@section('content')
<div class="row">
    <div class="{{ $alert_col }}">
        <div class="card">
            <div class="card-body">
                @include('messages.partials.details')

                <a href="{{ route('messages.reply', $message->uuid) }}" class="btn btn-primary btn-xs mt-4"><i class="fas fa-reply mr-2"></i>{{ _lang('Reply') }}</a>
                <a href="{{ route('messages.inbox') }}" class="btn btn-danger btn-xs mt-4"><i class="fas fa-envelope mr-2"></i>{{ _lang('Inbox') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
