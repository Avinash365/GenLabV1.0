<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'content' => $this->content,
            'file_path' => $this->file_path,
            'original_name' => $this->original_name,
            'sender_guard' => $this->sender_guard,
            'sender_name' => $this->sender_name,
            'reply_to_message_id' => $this->reply_to_message_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'reactions' => $this->whenLoaded('reactions', function () {
                return $this->reactions->groupBy('type')->map(function ($reactions, $type) {
                    return [
                        'type' => $type,
                        'count' => $reactions->count(),
                        'users' => UserResource::collection($reactions->load('user')->pluck('user')),
                    ];
                });
            }),
            'reply_to_message' => new ChatMessageResource($this->whenLoaded('replyToMessage')),
        ];
    }
}
