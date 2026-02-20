@extends('superadmin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">WhatsApp Chats (Replies)</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <p class="mb-4">Here are the replies received from customers via WhatsApp.</p>

            <div class="list-group">
                @if($chats->count() > 0)
                    @foreach($chats as $chat)
                        <a href="{{ route('whatsapp.chat.show', ['phoneNumber' => $chat->phone_number]) }}" class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">Phone: {{ $chat->phone_number }}</h5>
                                <small>{{ \Carbon\Carbon::parse($chat->last_activity)->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 text-truncate" style="max-width: 80%;">
                                @if($chat->last_message_type == 'image')
                                    <i class="fas fa-image"></i> [Image]
                                @elseif($chat->last_message_type == 'document')
                                    <i class="fas fa-file-alt"></i> [Document]
                                @else
                                    {{ $chat->last_message }}
                                @endif
                            </p>
                        </a>
                    @endforeach
                @else
                    <div class="alert alert-info">No messages received yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
