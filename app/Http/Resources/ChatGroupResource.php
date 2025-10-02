<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatGroupResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'members_count' => $this->whenLoaded('members', function () {
                return $this->members->count();
            }),
            'last_message' => new ChatMessageResource($this->whenLoaded('lastMessage')),
            'unread_count' => $this->when(isset($this->unread_count), $this->unread_count),
            'members' => UserResource::collection($this->whenLoaded('members')),
        ];
    }
}
