<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\ChatReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected function user()
    {
        return auth('web')->user() ?: auth('admin')->user();
    }

    public function groups()
    {
        // Ensure default groups exist
        $defaults = ['Bookings','Reports','Invoices','Management','Amendment Reports'];
        foreach ($defaults as $name) {
            ChatGroup::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
        return ChatGroup::orderBy('id')->get(['id','name'])->values();
    }

    public function messages(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id']);
        $user = $this->user();
        $list = ChatMessage::with(['user:id,name', 'reactions.user:id,name'])
            ->where('group_id', $request->integer('group_id'))
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->map(function(ChatMessage $m) use ($user) {
                return $this->serializeMessage($m, $user);
            });
        return response()->json($list);
    }

    public function messagesSince(Request $request)
    {
        $request->validate(['group_id' => 'required|integer|exists:chat_groups,id', 'after_id' => 'required|integer']);
        $user = $this->user();
        $list = ChatMessage::with(['user:id,name', 'reactions.user:id,name'])
            ->where('group_id', $request->integer('group_id'))
            ->where('id', '>', $request->integer('after_id'))
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->map(function(ChatMessage $m) use ($user) {
                return $this->serializeMessage($m, $user);
            });
        return response()->json($list);
    }

    public function send(Request $request)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate([
            'group_id' => 'required|integer|exists:chat_groups,id',
            'type' => 'required|string|in:text,image,pdf,voice',
            'content' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        $type = $request->string('type');
        $content = $request->string('content');
        $filePath = null; $original = null;

        if (in_array($type, ['image','pdf','voice'])) {
            $request->validate([
                'file' => [
                    'required','file','max:20480', // 20MB
                    function ($attr, $value, $fail) use ($type) {
                        $mime = $value->getMimeType();
                        if ($type === 'image' && !str_starts_with($mime, 'image/')) $fail('Invalid image file');
                        if ($type === 'pdf' && $mime !== 'application/pdf') $fail('Invalid PDF file');
                        if ($type === 'voice' && !str_starts_with($mime, 'audio/')) $fail('Invalid audio file');
                    }
                ]
            ]);
            $original = $request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->store('public/chat/'.$request->integer('group_id'));
        } else if ($type === 'text') {
            $request->validate(['content' => 'required|string|max:5000']);
        }

        $msg = ChatMessage::create([
            'group_id' => $request->integer('group_id'),
            'user_id' => $user->id,
            'type' => $type,
            'content' => $type === 'text' ? (string)$content : null,
            'file_path' => $filePath,
            'original_name' => $original,
        ]);

        $msg->load('user:id,name');
        return response()->json($this->serializeMessage($msg, $user), 201);
    }

    public function react(Request $request, ChatMessage $message)
    {
        $user = $this->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);
        $request->validate(['type' => 'required|string|max:32']);
        ChatReaction::updateOrCreate([
            'message_id' => $message->id,
            'user_id' => $user->id,
        ], [
            'type' => $request->string('type')
        ]);
        return response()->json(['status' => 'ok']);
    }

    protected function serializeMessage(ChatMessage $m, $viewer)
    {
        $fileUrl = $m->file_path ? Storage::url($m->file_path) : null;
        $data = [
            'id' => $m->id,
            'group_id' => $m->group_id,
            'user' => $m->relationLoaded('user') && $m->user ? [ 'id' => $m->user->id, 'name' => $m->user->name ] : null,
            'type' => $m->type,
            'content' => $m->content,
            'file_url' => $fileUrl,
            'original_name' => $m->original_name,
            'created_at' => $m->created_at?->toISOString(),
        ];
        // Reactions visible only to the message sender (owner)
        if ($viewer && $viewer->id === $m->user_id) {
            $data['reactions'] = $m->relationLoaded('reactions') ? $m->reactions->map(function(ChatReaction $r){
                return [ 'type' => $r->type, 'user_id' => $r->user_id, 'user' => $r->relationLoaded('user') && $r->user ? [ 'id'=>$r->user->id, 'name'=>$r->user->name ] : null ];
            })->values() : [];
        } else {
            $data['reactions'] = [];
        }
        return $data;
    }
}
