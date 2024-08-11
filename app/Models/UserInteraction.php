<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInteraction extends Model
{
    use HasFactory;

    protected $connection = 'second_database';
    protected $table = 'user_interactions';

    protected $fillable = [
        'recipient_id',
        'user_message',
        'bot_response',
        'type',
        'conversation'
    ];

    protected $casts = [
        'conversation' => 'array'
    ];
}
