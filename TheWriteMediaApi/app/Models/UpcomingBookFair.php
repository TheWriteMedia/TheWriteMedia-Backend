<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class UpcomingBookFair extends Model
{
    /** @use HasFactory<\Database\Factories\UpcomingBookFairFactory> */
    use HasFactory;

    
    protected $connection = 'mongodb';  
    protected $collection = 'upcomingbookfairs'; 

   protected $fillable = [
        'book_fair_title',
       'image_url',
       'logo_url',
       'month',
       'duration',
       'location',
       'summary',
       'status',
   ];


   protected static function boot()
   {
       parent::boot();

       // Automatically set 'status' to 'ACTIVE' when creating a news record
       static::creating(function ($upcomingBookFair) {
           if (!$upcomingBookFair->status) {
               $upcomingBookFair->status = 'ACTIVE';
           }
       });
   }
}
