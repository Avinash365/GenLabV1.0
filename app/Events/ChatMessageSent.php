<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast as ShouldBroadcastContract;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastContract
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payload;
    public $receiver_id;
    public $receiver_type;

    /**
     * Create a new event instance.
     */
    public function __construct(array $payload, $receiver_type = null, $receiver_id = null)
    {
        $this->payload = $payload;
        $this->receiver_id = $receiver_id;
        $this->receiver_type = $receiver_type;
    }

    public function broadcastAs()
    {
        return 'ChatMessageSent';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }

    /**
     * Get the channels the event should broadcast on.
     * We include type in channel name to disambiguate models.
     */
    public function broadcastOn()
    {
        $type = $this->receiver_type ?: 'user';
        $id = $this->receiver_id ?: '0';
        return new PrivateChannel("chat.user.{$type}.{$id}");
    }
}
