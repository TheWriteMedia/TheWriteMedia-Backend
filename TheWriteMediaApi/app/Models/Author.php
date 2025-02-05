<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use HasFactory;

    protected $connection = 'mongodb';  
     protected $collection = 'authors'; 

    protected $fillable = [
        'user_id',
        'author_name',
        'author_country',
        'author_age',
        'author_sex',
        'profile_picture',
    ];

    // Author Model
protected $casts = [
    '_id' => 'string',  // You might want to cast it to string if you are using object IDs
    'profile_picture' => 'string',
];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
