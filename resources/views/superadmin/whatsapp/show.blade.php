@extends('superadmin.layouts.app')

@section('content')
<style>
    .chat-container {
        height: 600px;
        overflow-y: scroll;
        border: 1px solid #ddd;
        padding: 15px;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
    }
    .message {
        max-width: 70%;
        margin-bottom: 15px;
        padding: 10px 15px;
        border-radius: 20px;
        position: relative;
        clear: both;
    }
    .message.received {
        background: #e9ecef;
        float: left;
        align-self: flex-start;
        border-bottom-left-radius: 0;
    }
    .message.sent {
        background: #dcf8c6; 
        float: right;
        align-self: flex-end;
        border-bottom-right-radius: 0;
    }
    .meta {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 5px;
        text-align: right;
    }
</style>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Chat with {{ $phoneNumber }}</h6>
            <a href="{{ route('whatsapp.chats.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="chat-container mb-3" id="chatContainer">
                @foreach($messages as $msg)
                    @if($msg->status == 'received')
                        <div class="message received">
                    @else
                        <div class="message sent">
                    @endif
                        
                        @if($msg->type == 'text')
                            <p class="mb-1">{{ $msg->message }}</p>
                        @elseif($msg->type == 'image')
                            <p class="mb-1 text-muted text-small">[Image]</p>
                            <!-- If you save media, you can display it here. Assuming URL is not public for now -->
                            <!-- <img src="{{ $msg->media_url }}" class="img-fluid rounded" style="max-width: 200px;"> -->
                        @elseif($msg->type == 'document')
                             <p class="mb-1"><i class="fas fa-file-pdf"></i> Document: {{ $msg->message }}</p>
                        @else
                             <p class="mb-1">[{{ ucfirst($msg->type) }}]</p>
                        @endif

                        <div class="meta">
                            {{ $msg->created_at->format('M d, H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Optional: Reply functionality -->
            <!-- 
            <form action="#" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" name="message" class="form-control" placeholder="Type a reply..." required>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </div>
                </div>
            </form>
            -->
        </div>
    </div>
</div>

<script>
    // Scroll to bottom
    var chatContainer = document.getElementById('chatContainer');
    chatContainer.scrollTop = chatContainer.scrollHeight;
</script>
@endsection
