<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class NewsBanner extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';  
    protected $collection = 'news_banners';
    
    protected $fillable = [
        'news_banner_one', // Will store array of image URLs
        'news_banner_two', // Will store array of image URLs
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'news_banner_one' => 'array',
        'news_banner_two' => 'array',
    ];

  
}