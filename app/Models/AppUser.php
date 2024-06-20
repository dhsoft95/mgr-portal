<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppUser extends Model
{
    use HasFactory;

    protected $table="users";

    protected $fillable = [
        'first_name',
        'last_name',
        'role_id',
        'email_verified_at',
        'password',
        'remember_token',
        'phone_number',
        'email',
        'identity_type',
        'identity_value',
        'birth_date',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'status',
        'otp_pin',
        'phone_verified_at',
    ];
}
