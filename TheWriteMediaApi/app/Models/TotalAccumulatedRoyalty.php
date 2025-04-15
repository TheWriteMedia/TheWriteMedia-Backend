<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class TotalAccumulatedRoyalty extends Model
{
    /** @use HasFactory<\Database\Factories\TotalAccumulatedRoyaltyFactory> */
    use HasFactory;

    
    protected $connection = 'mongodb';  
    protected $collection = 'total_accumulated_royalties'; 

   protected $fillable = [
        'user_id',
        'value',
   ];
   public function user()
   {
       return $this->belongsTo(User::class, 'user_id');
   }
}
