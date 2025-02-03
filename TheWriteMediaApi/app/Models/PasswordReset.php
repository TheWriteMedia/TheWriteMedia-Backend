<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PasswordReset extends Model
{
    protected $connection = 'mongodb'; // Specify the MongoDB connection
    protected $table = 'password_reset_tokens';

    protected $fillable = [
        'user_email',
        'token',
        'created_at',
    ];
    public $timestamps = false;
}