<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\DB;

class WhatsappChatController extends Controller
{
    public function index()
    {
        // Get list of unique phone numbers that have sent messages
        // Group by phone number and get the latest message
        // Relax status filter and strict grouping for now
        $chats = WhatsappMessage::select('phone_number')
            ->selectRaw('MAX(created_at) as last_activity')
            // ->where('status', 'received') // Allow seeing SENT messages too
            ->groupBy('phone_number')
            ->orderBy('last_activity', 'desc')
            ->get();

        // For each chat, fetch unread count (if you implement read status)
        // or just last message snippet
        foreach ($chats as $chat) {
            $lastMsg = WhatsappMessage::where('phone_number', $chat->phone_number)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($lastMsg) {
                $chat->last_message = $lastMsg->message;
                $chat->last_message_type = $lastMsg->type;
            } else {
                $chat->last_message = 'No messages';
                $chat->last_message_type = 'text';
            }
        }

        return view('superadmin.whatsapp.index', compact('chats'));
    }

    public function show($phoneNumber)
    {
        // Fetch all messages for this phone number
        $messages = WhatsappMessage::where('phone_number', $phoneNumber)
            ->orWhere('receiver_number', $phoneNumber) // Include outgoing if we store them here too
            ->orderBy('created_at', 'asc')
            ->get();

        return view('superadmin.whatsapp.show', compact('messages', 'phoneNumber'));
    }
}
