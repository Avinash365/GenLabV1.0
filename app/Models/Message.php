<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\User;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'sender_type',
        'receiver_type',
        'content',
        'read_at',
        'reactions',
    ];

    protected $casts = [
        'reactions' => 'array',
    ];

    // Return the sender model (Admin or Employee) if available
    public function senderModel()
    {
        if ($this->sender_type === 'admin') return Admin::find($this->sender_id);
        if ($this->sender_type === 'employee') return Employee::find($this->sender_id);
        if ($this->sender_type === 'user') return User::find($this->sender_id);
        return User::find($this->sender_id) ?: Admin::find($this->sender_id) ?: Employee::find($this->sender_id);
    }

    // Return the receiver model (Admin or Employee) if available
    public function receiverModel()
    {
        if ($this->receiver_type === 'admin') return Admin::find($this->receiver_id);
        if ($this->receiver_type === 'employee') return Employee::find($this->receiver_id);
        if ($this->receiver_type === 'user') return User::find($this->receiver_id);
        return User::find($this->receiver_id) ?: Admin::find($this->receiver_id) ?: Employee::find($this->receiver_id);
    }
}
