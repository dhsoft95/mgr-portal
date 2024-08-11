<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscalatedCase extends Model
{
    use HasFactory;
    protected $connection = 'second_database';
    protected $table = 'escalated_cases';

    protected $fillable = [
        'user_interaction_id',
        'recipient_id',
        'escalation_level',
        'status'
    ];

    public function userInteraction()
    {
        return $this->belongsTo(UserInteraction::class);
    }
}
