<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ChatMessageReacted implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $payload;
    protected $targets;

    public function __construct(array $payload, array $targets = [])
    {
        $this->payload = $payload;
        $this->targets = $targets;
    }

    public function broadcastAs()
    {
        return 'ChatMessageReacted';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }

    public function broadcastOn()
    {
        $channels = [];
        foreach ($this->targets as $t) {
            if (!isset($t['type']) || !isset($t['id'])) continue;
            $channels[] = new PrivateChannel("chat.user.{$t['type']}.{$t['id']}");
        }
        return $channels;
    }
}
