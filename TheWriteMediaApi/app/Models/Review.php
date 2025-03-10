<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    protected $connection = 'mongodb';  
    protected $collection = 'reviews'; 

   protected $fillable = [
        'book_id',
       'rating',
       'review_message',
       'status',
   ];

   protected static function boot()
   {
       parent::boot();

       // Automatically set 'status' to 'ACTIVE' when creating a news record
       static::creating(function ($news) {
           if (!$news->status) {
               $news->status = 'PENDING';
           }
       });
   }

}
