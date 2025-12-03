<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender',
        'body',
        'is_ai',
    ];

    protected $casts = [
        'is_ai' => 'boolean',
    ];
}
