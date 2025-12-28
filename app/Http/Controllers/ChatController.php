<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Message;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Events\ChatMessageSent;
use App\Events\ChatMessageReacted;

class ChatController extends Controller
{
    public function index()
    {
        $authUser = auth('web')->user() ?: auth('admin')->user();
        $myId = $authUser ? $authUser->id : null;
        $myType = null;
        if ($authUser instanceof Admin) $myType = 'admin';
        elseif ($authUser instanceof User) $myType = 'user';
        elseif ($authUser instanceof Employee) $myType = 'employee';

        // Collect admins and employees as chat contacts
        $admins = Admin::when(true, function($q) use ($myId, $myType) {
            if ($myType === 'admin') $q->where('id', '!=', $myId);
        })->get()->map(function($a){
            return (object)['id' => 'admin:'.$a->id, 'orig_id' => $a->id, 'name' => $a->name, 'type' => 'admin'];
        });
        $employees = Employee::when(true, function($q) use ($myId, $myType) {
            if ($myType === 'employee') $q->where('id', '!=', $myId);
        })->get()->map(function($e){
            return (object)['id' => 'employee:'.$e->id, 'orig_id' => $e->id, 'name' => $e->name, 'type' => 'employee'];
        });
        // include regular users from users table
        $usersList = \App\Models\User::when(true, function($q) use ($myId, $myType) {
            // if the current user is a regular user we'll exclude them
            if ($myType === 'user') $q->where('id', '!=', $myId);
        })->get()->map(function($u){
            return (object)['id' => 'user:'.$u->id, 'orig_id' => $u->id, 'name' => $u->name, 'type' => 'user'];
        });

        $users = $admins->concat($employees)->concat($usersList)->values();

        // Add a global Booking Group contact visible to all users/admins
        // Note: orig_id uses numeric group id (0) to remain compatible with integer DB columns
        $bookingGroup = (object)[
            'id' => 'group:booking',
            'orig_id' => 0,
            'name' => 'Booking Group',
            'type' => 'group',
        ];
        $users = $users->prepend($bookingGroup)->values();

        // remove contacts with empty or null names
        $users = $users->filter(function($c){
            return isset($c->name) && trim($c->name) !== '';
        })->values();

        // For each contact, compute last message and unread count relative to current user
        $users = $users->map(function($contact) use ($myId, $myType) {
            // build other descriptor
            $other = ['id' => $contact->orig_id, 'type' => $contact->type];

            $last = Message::where(function($q) use ($myId, $myType, $other) {
                $q->where('sender_id', $myId)->where('sender_type', $myType)->where('receiver_id', $other['id'])->where('receiver_type', $other['type']);
            })->orWhere(function($q) use ($myId, $myType, $other) {
                $q->where('sender_id', $other['id'])->where('sender_type', $other['type'])->where('receiver_id', $myId)->where('receiver_type', $myType);
            })->orderBy('created_at', 'desc')->first();

            // compute unread differently for group contacts
            $unread = 0;
            if (isset($contact->type) && $contact->type === 'group') {
                $groupKey = $contact->orig_id;
                if ($myType === 'admin') {
                    // admins see and count all unread group messages
                    $unread = Message::where('receiver_type', 'group')
                        ->where('receiver_id', $groupKey)
                        ->whereNull('read_at')
                        ->count();
                } else {
                    // regular users only see messages they sent and replies targeted at them.
                    // unread for them should count only replies targeted at them that are unread.
                    $all = Message::where('receiver_type', 'group')
                        ->where('receiver_id', $groupKey)
                        ->whereNull('read_at')
                        ->get();
                    $cnt = 0;
                    foreach ($all as $m) {
                        // skip messages authored by the user
                        if ($m->sender_id == $myId && ($m->sender_type == $myType || $m->sender_type === null)) continue;
                        $content = is_string($m->content) ? $m->content : '';
                        if (str_starts_with($content, 'REPLY::')) {
                            $rest = substr($content, strlen('REPLY::'));
                            $parts = explode('::', $rest, 2);
                            $jsonb64 = $parts[0] ?? null;
                            if ($jsonb64) {
                                try {
                                    $decoded = json_decode(base64_decode($jsonb64), true);
                                    if (is_array($decoded) && isset($decoded['visible_to'])) {
                                        if (intval($decoded['visible_to']) === intval($myId)) $cnt++;
                                    }
                                } catch (\Exception $e) { /* ignore parse errors */ }
                            }
                        }
                    }
                    $unread = $cnt;
                }
            } else {
                $unread = Message::where('sender_id', $other['id'])
                    ->where('sender_type', $other['type'])
                    ->where('receiver_id', $myId)
                    ->where('receiver_type', $myType)
                    ->whereNull('read_at')
                    ->count();
            }

            $contact->last_message = $last ? $last->content : null;
            $contact->last_message_at = $last ? $last->created_at : null;
            $contact->last_message_sender_id = $last ? ($last->sender_id ?? null) : null;
            $contact->last_message_sender_type = $last ? ($last->sender_type ?? null) : null;
            try {
                $contact->last_message_read_at = $last && $last->read_at ? ( $last->read_at instanceof \Carbon\Carbon ? $last->read_at->toRfc3339String() : (is_string($last->read_at) ? $last->read_at : null) ) : null;
            } catch (\Exception $e) { $contact->last_message_read_at = null; }
            $contact->unread_count = $unread;

            // compute avatar URL (prefer stored avatar or model fields, fallback to ui-avatars)
            $contact->avatar = $this->resolveAvatar($contact->type, $contact->orig_id, $contact->name ?? null);

            return $contact;
        });

        // sort contacts by most recent activity (last_message_at desc). Nulls appear last.
        $users = $users->sortByDesc(function($c){
            if (isset($c->last_message_at) && $c->last_message_at) return $c->last_message_at->getTimestamp();
            return 0;
        })->values();

        return view('chat', ['users' => $users, 'myId' => $myId, 'authUser' => $authUser, 'currentUserType' => $myType]);
    }

    // Return JSON messages between auth user and $user
    public function messages(Request $request, $user)
    {
        $authUser = auth('admin')->user() ?: auth('web')->user();
        $me = $authUser ? $authUser->id : null;
        $meType = null;
        if ($authUser instanceof Admin) $meType = 'admin';
        elseif ($authUser instanceof User) $meType = 'user';
        elseif ($authUser instanceof Employee) $meType = 'employee';

        $other = $this->parsePrefixedUser($user);

        // Special handling for group conversations
        if (isset($other['type']) && $other['type'] === 'group') {
            $groupKey = $other['id'];
            if ($meType === 'admin') {
                // Admins see all messages in the group
                $messages = Message::where('receiver_type', 'group')
                    ->where('receiver_id', $groupKey)
                    ->orderBy('created_at')
                    ->get();
            } else {
                // Regular users: fetch group messages then filter to include
                // - messages they sent, or
                // - messages that include a reply meta targeting them (visible_to)
                $all = Message::where('receiver_type', 'group')
                    ->where('receiver_id', $groupKey)
                    ->orderBy('created_at')
                    ->get();

                $messages = $all->filter(function($m) use ($me, $meType) {
                    if (!$m) return false;
                    if ($m->sender_id == $me && ($m->sender_type == $meType || $m->sender_type === null)) return true;
                    $content = is_string($m->content) ? $m->content : '';
                    if (str_starts_with($content, 'REPLY::')) {
                        $rest = substr($content, strlen('REPLY::'));
                        $parts = explode('::', $rest, 2);
                        $jsonb64 = $parts[0] ?? null;
                        if ($jsonb64) {
                            try {
                                $decoded = json_decode(base64_decode($jsonb64), true);
                                if (is_array($decoded) && isset($decoded['visible_to'])) {
                                    if (intval($decoded['visible_to']) === intval($me)) return true;
                                }
                            } catch (\Exception $e) { /* ignore parse errors */ }
                        }
                    }
                    return false;
                })->values();
            }
        } else {
            $messages = Message::where(function($q) use ($me, $meType, $other) {
                $q->where('sender_id', $me)
                  ->where('receiver_id', $other['id']);
                if ($meType) {
                    $q->where('sender_type', $meType);
                }
                if ($other['type']) {
                    $q->where('receiver_type', $other['type']);
                }
            })->orWhere(function($q) use ($me, $meType, $other) {
                $q->where('sender_id', $other['id'])
                  ->where('receiver_id', $me);
                if ($other['type']) {
                    $q->where('sender_type', $other['type']);
                }
                if ($meType) {
                    $q->where('receiver_type', $meType);
                }
            })->orderBy('created_at')->get();
        }

        // mark unread messages as read when the current user opens this conversation
        // Only mark as read when the client explicitly requests it via `?mark_read=1` to avoid
        // poll-based reads from clearing unread counts unintentionally.
        // For regular 1:1 chats we mark messages from the other participant as read.
        // For group chats: admins should mark all group messages as read; users mark only messages visible to them.
        $updatedIds = [];
        try {
            $shouldMark = false;
            try { $shouldMark = (bool) ($request->query('mark_read') || $request->input('mark_read')); } catch (\Exception $_) { $shouldMark = false; }
            if ($shouldMark) {
                if (!isset($other['type']) || $other['type'] !== 'group') {
                    $q = Message::where('sender_id', $other['id'])
                        ->where('sender_type', $other['type'])
                        ->where('receiver_id', $me)
                        ->where('receiver_type', $meType)
                        ->whereNull('read_at');
                    $updatedIds = $q->pluck('id')->toArray();
                    $q->update(['read_at' => Carbon::now()]);
                } else {
                    // group conversation
                    $groupKey = $other['id'];
                    if ($meType === 'admin') {
                        $q = Message::where('receiver_type', 'group')
                            ->where('receiver_id', $groupKey)
                            ->whereNull('read_at');
                        $updatedIds = $q->pluck('id')->toArray();
                        $q->update(['read_at' => Carbon::now()]);
                    } else {
                        // regular user: mark only messages that are visible to them (their own or replies targeted at them)
                        $all = Message::where('receiver_type', 'group')
                            ->where('receiver_id', $groupKey)
                            ->whereNull('read_at')
                            ->get();
                        $toMark = [];
                        foreach ($all as $m) {
                            if ($m->sender_id == $me && ($m->sender_type == $meType || $m->sender_type === null)) {
                                $toMark[] = $m->id;
                                continue;
                            }
                            $content = is_string($m->content) ? $m->content : '';
                            if (str_starts_with($content, 'REPLY::')) {
                                $rest = substr($content, strlen('REPLY::'));
                                $parts = explode('::', $rest, 2);
                                $jsonb64 = $parts[0] ?? null;
                                if ($jsonb64) {
                                    try {
                                        $decoded = json_decode(base64_decode($jsonb64), true);
                                        if (is_array($decoded) && isset($decoded['visible_to'])) {
                                            if (intval($decoded['visible_to']) === intval($me)) $toMark[] = $m->id;
                                        }
                                    } catch (\Exception $e) { /* ignore parse errors */ }
                                }
                            }
                        }
                        if (!empty($toMark)) {
                            Message::whereIn('id', $toMark)->update(['read_at' => Carbon::now()]);
                            $updatedIds = $toMark;
                        }
                    }
                }
            } else {
                // client didn't request marking messages as read — do nothing
                $updatedIds = [];
            }
        } catch (\Exception $e) {
            // ignore
        }

        // append sender/receiver display names and resolved avatars to each message for easier rendering
        $payload = $messages->map(function($m){
            $sender = $m->senderModel();
            $receiver = $m->receiverModel();
            $arr = array_merge($m->toArray(), [
                'sender_name' => $sender ? $sender->name : null,
                'receiver_name' => $receiver ? $receiver->name : null,
                'sender_avatar' => $this->resolveAvatar($m->sender_type ?? null, $m->sender_id ?? null, $sender?->name ?? null),
                'receiver_avatar' => $this->resolveAvatar($m->receiver_type ?? null, $m->receiver_id ?? null, $receiver?->name ?? null),
            ]);

            // standardize created_at to RFC3339 so clients parse times reliably with timezone
            try {
                if ($m->created_at instanceof \Carbon\Carbon) {
                    $arr['created_at'] = $m->created_at->toRfc3339String();
                } elseif (is_string($m->created_at) && trim($m->created_at) !== '') {
                    try { $arr['created_at'] = Carbon::parse($m->created_at)->toRfc3339String(); } catch (\Exception $ex) { $arr['created_at'] = $m->created_at; }
                } else {
                    $arr['created_at'] = null;
                }
            } catch (\Exception $e) {
                // fallback: leave as-is
            }
            // include read_at as RFC3339 so clients can show read receipts
            try {
                if ($m->read_at instanceof \Carbon\Carbon) {
                    $arr['read_at'] = $m->read_at->toRfc3339String();
                } elseif (is_string($m->read_at) && trim($m->read_at) !== '') {
                    try { $arr['read_at'] = Carbon::parse($m->read_at)->toRfc3339String(); } catch (\Exception $ex) { $arr['read_at'] = $m->read_at; }
                } else {
                    $arr['read_at'] = null;
                }
            } catch (\Exception $e) { /* ignore */ }

            // support reply marker: REPLY::base64(json)::rest
            $contentRaw = is_string($m->content) ? $m->content : '';
            $contentToProcess = $contentRaw;
            if (is_string($contentRaw) && str_starts_with($contentRaw, 'REPLY::')) {
                $rest = substr($contentRaw, strlen('REPLY::'));
                $parts = explode('::', $rest, 2);
                $jsonb64 = $parts[0] ?? null;
                $inner = $parts[1] ?? '';
                try {
                    $decoded = json_decode(base64_decode($jsonb64), true);
                    if (is_array($decoded)) $arr['reply_to'] = $decoded;
                } catch (\Exception $e) { /* ignore */ }
                $contentToProcess = $inner;
            }

            // detect audio marker stored in content
            if (is_string($contentToProcess) && str_starts_with($contentToProcess, 'AUDIO::')) {
                $path = substr($contentToProcess, strlen('AUDIO::'));
                try {
                    $arr['audio_url'] = Storage::url($path);
                } catch (\Exception $e) {
                    $arr['audio_url'] = null;
                }
                $arr['content'] = '[Audio]';
            }
            // detect file/attachment markers stored in content
            if (is_string($contentToProcess) && str_starts_with($contentToProcess, 'FILE::')) {
                // support markers saved as FILE::{path} or FILE::{path}::{urlencoded-original-name}
                $rest = substr($contentToProcess, strlen('FILE::'));
                $parts = explode('::', $rest, 2);
                $path = $parts[0] ?? null;
                $origName = null;
                if (isset($parts[1])) {
                    // if the stored marker included the encoded original name
                    $origName = rawurldecode($parts[1]);
                }
                try {
                    $arr['file_url'] = $path ? Storage::url($path) : null;
                } catch (\Exception $e) {
                    $arr['file_url'] = null;
                }
                // try to include size if file exists in storage
                $size = null;
                try {
                    if ($path && Storage::disk('public')->exists($path)) {
                        $size = Storage::disk('public')->size($path);
                    }
                } catch (\Exception $e) { /* ignore */ }

                $att = ['url' => $arr['file_url'], 'path' => $path];
                if ($origName) $att['name'] = $origName;
                if (!is_null($size)) $att['size'] = $size;
                $arr['attachments'] = [$att];
                $arr['content'] = '[Attachment]';
            }

            // If no special marker matched, expose the cleaned content (without REPLY:: prefix)
            if (!isset($arr['audio_url']) && !isset($arr['file_url'])) {
                // prefer processed contentToProcess (plain text)
                $cleanContent = is_string($contentToProcess) ? $contentToProcess : ($arr['content'] ?? '');
                try {
                    // remove legacy client-side reply prefixes like "↪ Name: " or "↪ Someone 9:55 PM: "
                    $cleanContent = preg_replace('/^↪.*?:\s*/u', '', $cleanContent);
                    // remove leading time-like prefixes such as "9:55 PM: " or "14 AM: " or "You 9:55 PM: "
                    $cleanContent = preg_replace('/^\s*(?:You\s*)?\d{1,2}:\d{2}\s*(?:AM|PM)?\s*[:\-–]?\s*/i', '', $cleanContent);
                    // remove patterns like "Name 9:55 PM: " (name followed by time)
                    $cleanContent = preg_replace('/^\s*[A-Za-z0-9\s]{1,60}\d{1,2}:\d{2}\s*(?:AM|PM)?\s*[:\-–]?\s*/i', '', $cleanContent);
                    // fallback: if still begins with something like "14 AM: " (hour + AM/PM + colon)
                    $cleanContent = preg_replace('/^\s*\d{1,2}\s*(?:AM|PM)\s*[:\-–]?\s*/i', '', $cleanContent);
                } catch (\Exception $e) { /* ignore */ }
                $arr['content'] = $cleanContent;
            }

            return $arr;
        });

        // If we updated any messages to mark them as read, broadcast a read receipt to the sender (other user)
        try {
            if (!empty($updatedIds)) {
                // notify the other participant that their messages were read
                event(new \App\Events\ChatMessageRead([
                    'reader_id' => $me,
                    'reader_type' => $meType,
                    'message_ids' => $updatedIds,
                    'read_at' => Carbon::now()->toRfc3339String()
                ], $other['type'] ?? null, $other['id'] ?? null));
            }
        } catch (\Exception $e) {
            // ignore broadcasting errors
        }

        return response()->json($payload);
    }

    // Search contacts (admins, employees, users) by name
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));
        $authUser = auth('web')->user() ?: auth('admin')->user();
        $myId = $authUser ? $authUser->id : null;
        $myType = null;
        if ($authUser instanceof Admin) $myType = 'admin';
        elseif ($authUser instanceof User) $myType = 'user';
        elseif ($authUser instanceof Employee) $myType = 'employee';

        if ($q === '') {
            return response()->json([]);
        }

        $admins = Admin::where('name', 'like', "%{$q}%")
            ->when($myType === 'admin', fn($query) => $query->where('id', '!=', $myId))
            ->limit(30)
            ->get()
            ->map(fn($a) => ['id' => 'admin:'.$a->id, 'orig_id' => $a->id, 'name' => $a->name, 'type' => 'admin']);

        $employees = Employee::where(function($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(last_name, '')) like ?", ["%{$q}%"]);
            })
            ->when($myType === 'employee', fn($query) => $query->where('id', '!=', $myId))
            ->limit(30)
            ->get()
            ->map(fn($e) => ['id' => 'employee:'.$e->id, 'orig_id' => $e->id, 'name' => ($e->full_name ?? trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? ''))), 'type' => 'employee']);

        $users = User::whereNotNull('name')->where('name', '<>', '')->where('name', 'like', "%{$q}%")
            ->when($myType === 'user', fn($query) => $query->where('id', '!=', $myId))
            ->limit(30)
            ->get()
            ->map(fn($u) => ['id' => 'user:'.$u->id, 'orig_id' => $u->id, 'name' => $u->name, 'type' => 'user']);

        $results = $admins->concat($employees)->concat($users)->values();

        // attach avatar for each result
        $results = $results->map(function($r){
            $r['avatar'] = $this->resolveAvatar($r['type'] ?? null, $r['orig_id'] ?? null, $r['name'] ?? null);
            return $r;
        });

        return response()->json($results);
    }

    // Return contacts summary as JSON for client polling
    public function contacts(Request $request)
    {
        $authUser = auth('web')->user() ?: auth('admin')->user();
        $myId = $authUser ? $authUser->id : null;
        $myType = null;
        if ($authUser instanceof Admin) $myType = 'admin';
        elseif ($authUser instanceof User) $myType = 'user';
        elseif ($authUser instanceof Employee) $myType = 'employee';

        $admins = Admin::when(true, function($q) use ($myId, $myType) {
            if ($myType === 'admin') $q->where('id', '!=', $myId);
        })->get()->map(function($a){
            return (object)['id' => 'admin:'.$a->id, 'orig_id' => $a->id, 'name' => $a->name, 'type' => 'admin'];
        });
        $employees = Employee::when(true, function($q) use ($myId, $myType) {
            if ($myType === 'employee') $q->where('id', '!=', $myId);
        })->get()->map(function($e){
            return (object)['id' => 'employee:'.$e->id, 'orig_id' => $e->id, 'name' => $e->name, 'type' => 'employee'];
        });
        $usersList = User::when(true, function($q) use ($myId, $myType) {
            if ($myType === 'user') $q->where('id', '!=', $myId);
        })->get()->map(function($u){
            return (object)['id' => 'user:'.$u->id, 'orig_id' => $u->id, 'name' => $u->name, 'type' => 'user'];
        });

        $users = $admins->concat($employees)->concat($usersList)->values();
        // Prepend Booking Group so it appears in contacts for everyone
        // Use numeric orig_id (0) so contacts/messages queries remain compatible with integer columns
        $bookingGroup = (object)[
            'id' => 'group:booking',
            'orig_id' => 0,
            'name' => 'Booking Group',
            'type' => 'group',
        ];
        $users = $users->prepend($bookingGroup)->values();
        $users = $users->filter(function($c){ return isset($c->name) && trim($c->name) !== ''; })->values();

        $users = $users->map(function($contact) use ($myId, $myType) {
            $other = ['id' => $contact->orig_id, 'type' => $contact->type];

            // Special handling for Booking Group contacts
            if (isset($other['type']) && $other['type'] === 'group') {
                $groupKey = $other['id'];
                if ($myType === 'admin') {
                    $last = Message::where('receiver_type', 'group')
                        ->where('receiver_id', $groupKey)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    $unread = Message::where('receiver_type', 'group')
                        ->where('receiver_id', $groupKey)
                        ->whereNull('read_at')
                        ->count();
                } else {
                    // regular user: only consider messages they sent to the group
                    $last = Message::where('receiver_type', 'group')
                        ->where('receiver_id', $groupKey)
                        ->where('sender_id', $myId)
                        ->where('sender_type', $myType)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    $unread = 0;
                }
            } else {
                $last = Message::where(function($q) use ($myId, $myType, $other) {
                    $q->where('sender_id', $myId)->where('sender_type', $myType)->where('receiver_id', $other['id'])->where('receiver_type', $other['type']);
                })->orWhere(function($q) use ($myId, $myType, $other) {
                    $q->where('sender_id', $other['id'])->where('sender_type', $other['type'])->where('receiver_id', $myId)->where('receiver_type', $myType);
                })->orderBy('created_at', 'desc')->first();

                $unread = Message::where('sender_id', $other['id'])
                    ->where('sender_type', $other['type'])
                    ->where('receiver_id', $myId)
                    ->where('receiver_type', $myType)
                    ->whereNull('read_at')
                    ->count();
            }

            $contact->last_message = $last ? $last->content : null;
            $contact->last_message_at = $last ? $last->created_at : null;
            $contact->unread_count = $unread;
            $contact->avatar = $this->resolveAvatar($contact->type, $contact->orig_id, $contact->name ?? null);
            return $contact;
        })->sortByDesc(function($c){
            if (isset($c->last_message_at) && $c->last_message_at) return $c->last_message_at->getTimestamp();
            return 0;
        })->values();

        $payload = $users->map(function($c){
            $lm = $c->last_message;
            if (is_string($lm)) {
                if (str_starts_with($lm, 'AUDIO::')) $lm = 'Audio';
                elseif (str_starts_with($lm, 'FILE::')) $lm = 'Attachment';
            }
            return [
                'id' => $c->id,
                'orig_id' => $c->orig_id,
                'name' => $c->name,
                'type' => $c->type,
                'avatar' => $c->avatar,
                'last_message' => $lm,
                // use RFC3339 so client interprets timezone correctly
                'last_message_at' => $c->last_message_at ? $c->last_message_at->toRfc3339String() : null,
                'last_message_sender_orig_id' => $c->last_message_sender_id ?? null,
                'last_message_sender_type' => $c->last_message_sender_type ?? null,
                'last_message_read_at' => $c->last_message_read_at ?? null,
                'unread_count' => $c->unread_count ?? 0,
            ];
        });

        return response()->json($payload);
    }

    // Resolve avatar URL for a given contact type/id
    protected function resolveAvatar($type, $id, $name = null)
    {
        $name = $name ?: 'User';
        // try stored avatars in public storage: avatars/{id}.{ext}
        if ($id) {
            foreach (['png','jpg','jpeg','webp'] as $ext) {
                if (Storage::disk('public')->exists("avatars/{$id}.{$ext}")) {
                    return Storage::url("avatars/{$id}.{$ext}");
                }
            }
        }

        // try to pull from model fields if available
        try {
            if ($type === 'admin' && $id) {
                $m = Admin::find($id);
            } elseif ($type === 'employee' && $id) {
                $m = Employee::find($id);
            } elseif ($type === 'user' && $id) {
                $m = User::find($id);
            } else {
                $m = null;
            }
            if ($m) {
                $avatarField = $m->profile_photo_url ?? $m->avatar ?? $m->photo ?? null;
                if (is_string($avatarField) && $avatarField) {
                    if (str_starts_with($avatarField, 'data:') || str_starts_with($avatarField, 'http')) {
                        return $avatarField;
                    }
                    if (Storage::disk('public')->exists($avatarField)) {
                        return Storage::url($avatarField);
                    } elseif (Storage::disk('public')->exists('avatars/'.$avatarField)) {
                        return Storage::url('avatars/'.$avatarField);
                    } elseif (file_exists(public_path($avatarField))) {
                        return asset($avatarField);
                    } elseif (file_exists(public_path('storage/'.ltrim($avatarField, '/')))) {
                        return asset('storage/'.ltrim($avatarField, '/'));
                    }
                }
            }
        } catch (\Exception $e) {
            // ignore
        }

        // fallback to ui-avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=ffffff&color=0D8ABC&size=128';
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required',
            'content' => 'nullable|string',
            // Accept audio or generic file uploads up to 10MB
            'audio' => 'nullable|file|max:10240',
            'files' => 'nullable|file|max:10240',
        ]);

        $authUser = auth('admin')->user() ?: auth('web')->user();
        $senderId = $authUser ? $authUser->id : null;
        $senderType = null;
        if ($authUser instanceof Admin) $senderType = 'admin';
        elseif ($authUser instanceof User) $senderType = 'user';
        elseif ($authUser instanceof Employee) $senderType = 'employee';

        $other = $this->parsePrefixedUser($request->receiver_id);

        // If an audio file was uploaded, store it and save a marker in content so it can
        // be rendered as an audio message on the client.
        $storedContent = $request->input('content', null);
        $audioUrl = null;
        $fileUrl = null;
        $attachments = [];

        // handle audio file (voice messages)
        if ($request->hasFile('audio')) {
            try {
                $path = $request->file('audio')->store('chat_audio', 'public');
                $storedContent = 'AUDIO::' . $path; // marker so we can detect audio messages
                $audioUrl = Storage::url($path);
            } catch (\Exception $e) {
                // continue without audio
            }
        }

        // handle generic file upload (single file input named 'files')
        if ($request->hasFile('files')) {
            try {
                $f = $request->file('files');
                $path = $f->store('chat_files', 'public');
                // store marker so client can detect attachments; include original filename (url-encoded)
                $origName = rawurlencode($f->getClientOriginalName());
                $storedContent = 'FILE::' . $path . '::' . $origName;
                $fileUrl = Storage::url($path);
                $attachments[] = ['url' => $fileUrl, 'path' => $path, 'name' => $f->getClientOriginalName(), 'size' => $f->getSize()];
            } catch (\Exception $e) {
                // ignore
            }
        }

        // If this is a reply to an existing message, embed reply metadata at the start of the stored content
        $replyMeta = null;
        try {
            $replyTo = $request->input('reply_to', null);
            if ($replyTo) {
                $orig = Message::find($replyTo);
                if ($orig) {
                    // Build a clean snippet: strip any REPLY:: markers and any client-inserted reply prefixes like "↪ Name: "
                    $rawSnippet = is_string($orig->content) ? $orig->content : '';
                    try {
                        if (is_string($rawSnippet) && str_starts_with($rawSnippet, 'REPLY::')) {
                            $r = substr($rawSnippet, strlen('REPLY::'));
                            $parts = explode('::', $r, 2);
                            $inner = $parts[1] ?? $parts[0] ?? '';
                            $rawSnippet = $inner;
                        }
                        // Remove leading client-side reply prefixes like "↪ Someone: " (up to 200 chars)
                        $rawSnippet = preg_replace('/^↪[^:]{0,200}:\s*/u', '', $rawSnippet);

                        // If the original content is a FILE:: marker, try to extract a friendly filename
                        if (is_string($rawSnippet) && strpos($rawSnippet, 'FILE::') !== false) {
                            try {
                                $after = substr($rawSnippet, strpos($rawSnippet, 'FILE::') + 6);
                                // support FILE::path::encodedName or FILE::path <space> name
                                $parts = explode('::', $after, 2);
                                $path = $parts[0] ?? null;
                                $origName = null;
                                if (isset($parts[1]) && trim($parts[1]) !== '') {
                                    $origName = rawurldecode($parts[1]);
                                } else {
                                    // check for trailing plain filename after the path
                                    $tokens = preg_split('/\s+/', $after, 2);
                                    if (isset($tokens[1]) && trim($tokens[1]) !== '') $origName = trim($tokens[1]);
                                }
                                if ($origName) {
                                    $rawSnippet = $origName;
                                } else {
                                    // fallback to basename of the storage path
                                    if ($path) $rawSnippet = basename($path);
                                }
                            } catch (\Exception $__e) { /* ignore parsing errors */ }
                        }

                        $clean = strip_tags($rawSnippet);
                        $clean = trim($clean);
                        $clean = mb_substr($clean, 0, 160);
                    } catch (\Exception $e) { $clean = is_string($orig->content) ? mb_substr(strip_tags($orig->content), 0, 160) : null; }

                    $meta = [
                        'id' => $orig->id,
                        'sender_name' => $orig->senderModel()?->name ?? null,
                        'snippet' => $clean,
                    ];
                    // attach visibility so admin replies in group can be targeted to the original user
                    $meta['visible_to'] = $orig->sender_id;
                    $meta['visible_to_type'] = $orig->sender_type;
                    $replyMeta = $meta;
                    $storedContent = 'REPLY::' . base64_encode(json_encode($meta)) . '::' . ($storedContent ?? '');
                }
            }
        } catch (\Exception $e) { /* ignore reply packing errors */ }

        $msg = Message::create([
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'receiver_id' => $other['id'],
            'receiver_type' => $other['type'],
            'content' => $storedContent,
        ]);

        // resolve friendly names for response
        $senderModel = $msg->senderModel();
        $receiverModel = $msg->receiverModel();
        $payload = array_merge($msg->toArray(), [
            'sender_name' => $senderModel ? $senderModel->name : null,
            'receiver_name' => $receiverModel ? $receiverModel->name : null,
            'sender_avatar' => $this->resolveAvatar($senderType, $senderId, $senderModel?->name ?? null),
            'receiver_avatar' => $this->resolveAvatar($other['type'] ?? null, $other['id'] ?? null, $receiverModel?->name ?? null),
        ]);

        // If we stored an audio file, include its public URL in the response payload and
        // replace the content for preview purposes.
        if ($audioUrl) {
            $payload['audio_url'] = $audioUrl;
            $payload['content'] = '[Audio]';
        }
        // If we stored a generic file, include its public URL and attachments info
        if ($fileUrl) {
            $payload['file_url'] = $fileUrl;
            $payload['attachments'] = $attachments;
            $payload['content'] = '[Attachment]';
        }

        // ensure created_at in response uses RFC3339 so clients parse it reliably
        try {
            $payload['created_at'] = $msg->created_at ? $msg->created_at->toRfc3339String() : null;
        } catch (\Exception $e) { /* ignore */ }

        // log for debugging
        try {
            \Log::info('Chat message created', $payload);
        } catch (\Exception $e) {
            // ignore logging errors
        }

        // include reply metadata in payload when available so clients can render quoted-replies immediately
        if (isset($replyMeta) && $replyMeta) {
            $payload['reply_to'] = $replyMeta;
        }

        // include original client receiver pref if available so clients can match group views
        try {
            $payload['receiver_pref'] = $request->input('receiver_id') ?? (($other['type'] ?? '') . ':' . ($other['id'] ?? ''));
        } catch (\Exception $e) { /* ignore */ }

        // broadcast to recipient so their sidebar updates instantly if using Echo/Pusher
        try {
            event(new ChatMessageSent($payload, $other['type'] ?? null, $other['id'] ?? null));

            // If this was an admin reply inside a group directed at a user's original message,
            // also create and broadcast a personal copy to the original message sender so they
            // receive the reply directly in their inbox.
            if (isset($other['type']) && $other['type'] === 'group' && $senderType === 'admin' && isset($replyMeta) && isset($replyMeta['id'])) {
                try {
                    $orig = Message::find($replyMeta['id']);
                    if ($orig && $orig->sender_id) {
                        // Broadcast the same group-stored payload to the original user's private channel
                        // so they see the admin's reply in real-time while the message remains stored as a group message.
                        event(new ChatMessageSent($payload, $orig->sender_type, $orig->sender_id));
                    }
                } catch (\Exception $ex) {
                    // ignore personal broadcast errors
                }
            }
        } catch (\Exception $e) {
            // ignore broadcast errors (fallback to polling)
        }

        return response()->json($payload, 201);
    }

    // Receive typing notifications from client and broadcast to recipient
    public function typing(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required',
            'typing' => 'nullable|boolean',
        ]);

        $authUser = auth('admin')->user() ?: auth('web')->user();
        $senderId = $authUser ? $authUser->id : null;
        $senderType = null;
        if ($authUser instanceof Admin) $senderType = 'admin';
        elseif ($authUser instanceof User) $senderType = 'user';
        elseif ($authUser instanceof Employee) $senderType = 'employee';

        $other = $this->parsePrefixedUser($request->receiver_id);

        $payload = [
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'typing' => (bool) ($request->input('typing') ?: false),
            'timestamp' => now()->toRfc3339String(),
        ];

        try {
            event(new \App\Events\ChatTyping($payload, $other['type'] ?? null, $other['id'] ?? null));
        } catch (\Exception $e) {
            // ignore broadcast failures
        }

        return response()->json(['ok' => true]);
    }

    // Handle reactions to messages (emoji reactions)
    public function reaction(Request $request)
    {
        $request->validate([
            'message_id' => 'required',
            'emoji' => 'required|string|max:8',
        ]);

        $authUser = auth('admin')->user() ?: auth('web')->user();
        $reactorId = $authUser ? $authUser->id : null;
        $reactorType = null;
        if ($authUser instanceof Admin) $reactorType = 'admin';
        elseif ($authUser instanceof User) $reactorType = 'user';
        elseif ($authUser instanceof Employee) $reactorType = 'employee';

        $message = Message::find($request->input('message_id'));
        if (!$message) return response()->json(['error' => 'Message not found'], 404);

        // determine the other participant so we can notify them
        if ($message->sender_id == $reactorId && ($message->sender_type == $reactorType || $message->sender_type === null)) {
            $otherId = $message->receiver_id;
            $otherType = $message->receiver_type;
        } else {
            $otherId = $message->sender_id;
            $otherType = $message->sender_type;
        }

        $reactorModel = $authUser;
        $payload = [
            'message_id' => $message->id,
            'emoji' => $request->input('emoji'),
            'reactor_id' => $reactorId,
            'reactor_type' => $reactorType,
            'reactor_name' => $reactorModel ? ($reactorModel->name ?? null) : null,
            'reactor_avatar' => $this->resolveAvatar($reactorType, $reactorId, $reactorModel?->name ?? null),
            'timestamp' => now()->toRfc3339String(),
        ];

        try {
            // broadcast the reaction to both participants (reactor and other)
            $targets = [];
            if ($otherType && $otherId) $targets[] = ['type' => $otherType, 'id' => $otherId];
            if ($reactorType && $reactorId) $targets[] = ['type' => $reactorType, 'id' => $reactorId];
            // persist reaction users on the message record so we can toggle per-user reactions
            try {
                $emoji = $request->input('emoji');
                $userKey = ($reactorType ? $reactorType : 'user') . ':' . $reactorId;
                $current = $message->reactions ?? [];
                if (!is_array($current)) {
                    $current = json_decode($current, true) ?: [];
                }

                // ensure arrays for each emoji
                foreach ($current as $k => $v) {
                    if (!is_array($current[$k])) {
                        $current[$k] = is_null($v) ? [] : (is_numeric($v) ? [] : (array)$v);
                    }
                }

                $acted = false;
                $selectedEmoji = null;

                // If user is already present under this emoji, remove (toggle off)
                if (isset($current[$emoji]) && in_array($userKey, $current[$emoji])) {
                    $current[$emoji] = array_values(array_filter($current[$emoji], function($x) use ($userKey) { return $x !== $userKey; }));
                    $acted = false;
                } else {
                    // Add user to this emoji and remove them from any other emoji lists
                    // First remove from any other emoji arrays
                    foreach ($current as $k => $arr) {
                        if (!is_array($arr)) continue;
                        $current[$k] = array_values(array_filter($arr, function($x) use ($userKey) { return $x !== $userKey; }));
                    }
                    // Then add to requested emoji
                    if (!isset($current[$emoji]) || !is_array($current[$emoji])) $current[$emoji] = [];
                    $current[$emoji][] = $userKey;
                    // dedupe
                    $current[$emoji] = array_values(array_unique($current[$emoji]));
                    $acted = true;
                    $selectedEmoji = $emoji;
                }

                // remove empty emoji lists to avoid storing zero-count keys
                foreach ($current as $k => $arr) {
                    if (is_array($arr) && count($arr) === 0) unset($current[$k]);
                }
                // save updated map
                $message->reactions = $current;
                $message->save();

                // prepare counts mapping for clients
                $counts = [];
                foreach ($current as $k => $arr) {
                    $counts[$k] = is_array($arr) ? count($arr) : (int)$arr;
                }
                $payload['reactions'] = $counts;
                $payload['reaction_users'] = $current;
                $payload['acted'] = $acted;
                $payload['selected_emoji'] = $selectedEmoji;
            } catch (\Exception $e) {
                // ignore persistence errors but continue to broadcast
            }
            event(new ChatMessageReacted($payload, $targets));
        } catch (\Exception $e) {
            // ignore broadcast errors
        }

        return response()->json($payload);
    }

    protected function parsePrefixedUser($val)
    {
        if (is_string($val) && strpos($val, ':') !== false) {
            [$prefix, $id] = explode(':', $val, 2);
            // map group keys to numeric ids for DB compatibility (e.g., 'booking' -> 0)
            if ($prefix === 'group') {
                $map = [
                    'booking' => 0,
                ];
                $gid = $map[strval($id)] ?? ($this->isNumericString($id) ? (int)$id : 0);
                return ['type' => $prefix, 'id' => $gid];
            }
            if (is_numeric($id)) {
                return ['type' => $prefix, 'id' => (int)$id];
            }
            return ['type' => $prefix, 'id' => $id];
        }
        if (is_numeric($val)) return ['type' => null, 'id' => (int)$val];
        if (is_string($val) && $val !== '') return ['type' => null, 'id' => $val];
        return ['type' => null, 'id' => null];
    }

    protected function isNumericString($v)
    {
        return is_string($v) && preg_match('/^\d+$/', $v);
    }
}
