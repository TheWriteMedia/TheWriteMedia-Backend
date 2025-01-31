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
        'author_sex'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
