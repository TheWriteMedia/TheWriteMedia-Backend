<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class News extends Model
{
    /** @use HasFactory<\Database\Factories\NewsFactory> */
    use HasFactory;

    protected $connection = 'mongodb';  
    protected $collection = 'news'; 

   protected $fillable = [
       'user_id',
       'news_title',
       'news_description',
       'conclusion',
       'template_no',
       'news_plugs',
       'img_urls',
       'status'
   ];

   protected static function boot()
   {
       parent::boot();

       // Automatically set 'status' to 'ACTIVE' when creating a news record
       static::creating(function ($news) {
           if (!$news->status) {
               $news->status = 'ACTIVE';
           }
       });
   }

   public function user()
   {
       return $this->belongsTo(User::class, 'user_id');
   }
   
}
