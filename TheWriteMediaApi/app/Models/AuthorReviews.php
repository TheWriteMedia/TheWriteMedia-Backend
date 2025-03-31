<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class AuthorReviews extends Model
{
     /** @use HasFactory<\Database\Factories\ReviewFactory> */
     use HasFactory;

     protected $connection = 'mongodb';  
     protected $collection = 'author_reviews'; 
 
    protected $fillable = [
        'author_name',
        'author_type',
        'img_url',
        'review_message',
        'status',
    ];
 
    protected static function boot()
    {
        parent::boot();
 
        // Automatically set 'status' to 'ACTIVE' when creating a news record
        static::creating(function ($author_reviews) {
            if (!$author_reviews->status) {
                $author_reviews->status = 'ACTIVE';
            }
        });
    }
}
