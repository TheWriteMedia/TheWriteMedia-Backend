<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

     protected $connection = 'mongodb';  
     protected $collection = 'users'; 

    protected $fillable = [
        'user_name',
        'user_email',
        'user_password',
        'user_type',
        'user_profile',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'user_password' => 'hashed',
        ];
    }

     // Define constants for user types
     const USER_TYPE_WEB_ADMIN = 'web_admin';
     const USER_TYPE_AUTHOR = 'author';
   
 
       // Default status for newly registered users
       const STATUS_ACTIVE = 'ACTIVE';
     
       // Automatically set the status to ACTIVE when a user is created
       protected static function booted()
       {
           static::creating(function ($user) {
               // If the status is not set, set it to 'ACTIVE'
               if (!$user->status) {
                   $user->status = self::STATUS_ACTIVE;
               }
           });
       }
       public function author()
    {
      // Specify the foreign key explicitly if it’s not the default
    return $this->hasOne(Author::class, 'user_id', '_id');
    }
}
