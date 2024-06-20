<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notrans extends Model
{
    use HasFactory;
    protected $table="users";


    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\NotranScope());
    }
}
