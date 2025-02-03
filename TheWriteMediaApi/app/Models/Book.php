<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;


    protected $connection = 'mongodb';  
    protected $collection = 'books'; 

   protected $fillable = [
       'user_id',
       'author_name',
       'book_title',
       'ebook_price',
       'paperback_price',
       'paperback_isbn',
       'ebook_isbn',
       'img_urls',
       'status'
   ];

   protected static function boot()
   {
       parent::boot();

       // Automatically set 'status' to 'ACTIVE' when creating a news record
       static::creating(function ($book) {
           if (!$book->status) {
               $book->status = 'ACTIVE';
           }
       });
   }

   public function user()
   {
       return $this->belongsTo(User::class);
   }
}
