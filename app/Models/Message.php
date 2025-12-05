<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender',
        'body',
        'is_ai',
        'chat_id',
        'sender_id',
    ];

    protected $casts = [
        'is_ai' => 'boolean',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
