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
        'start_date',
        'end_date',
        'location',
        'summary',
        'detailed_description', // Added field,
        'theme_color', 
        'services', // Added field for services array
        'compiled_images_url', // Add this line
        'status',
   ];

   
   protected $casts = [
    'services' => 'array', // Cast services to array
    'compiled_images_url' => 'array', // Add this cast
        'start_date' => 'date',
        'end_date' => 'date'
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
