 <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    if (!window.__CHAT_PUSHER_BOUND__) {
        window.__CHAT_PUSHER_BOUND__ = true;
        Pusher.logToConsole = true;
        var pusher = new Pusher('500d2fa7a4b11dbfeb91', {
            cluster: 'ap2',
            forceTLS: true
        });
        var channel = pusher.subscribe('chat');
        function handleChatEvent(data) {
            console.log('Pusher event received:', data);
            // Update your chat UI here, e.g.:
            // $('#chat-messages').append('<div>' + (data.message ? data.message.content : data.content) + '</div>');
        }
        channel.bind('App\\Events\\MessageSent', handleChatEvent);
        channel.bind('ChatMessageBroadcast', handleChatEvent);
    } else {
        console.log('[chat.blade] Pusher already initialized; skipping duplicate bind');
    }
</script>