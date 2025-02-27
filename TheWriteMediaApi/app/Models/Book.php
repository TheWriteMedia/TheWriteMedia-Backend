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
       'author_id',
       'book_title',
      
       'paperback_price_increase',
       'paperback_srp',
       'paperback_price',
       'paperback_isbn',
       
       'hardback_price_increase',
       'hardback_srp',
       'hardback_price',
       'hardback_isbn',

       'ebook_price_increase',
       'ebook_srp',
       'ebook_price',
       'ebook_isbn',

       'description',
       'additional_info',
       'img_urls',
       'status'
   ];

   

   protected $casts = [
    'paperback_price_increase' => 'double',
    'paperback_srp' => 'double',
    'paperback_price' => 'double',
    
    'hardback_price_increase' => 'double',
    'hardback_srp' => 'double',
    'hardback_price' => 'double',

    'ebook_price_increase' => 'double',
    'ebook_srp' => 'double',
    'ebook_price' => 'double',
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

   public function author()
{
    return $this->belongsTo(Author::class, 'author_id', '_id');
}

}
