<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class WithdrawalRequest extends Model
{
    /** @use HasFactory<\Database\Factories\TotalAccumulatedRoyaltyFactory> */
    use HasFactory;

    
    protected $connection = 'mongodb';  
    protected $collection = 'withdrawal_requests'; 

   protected $fillable = [
        'user_id',
        'name',
        'withdraw_value',
        'mailing_address',
        'date_received',
        'status'
   ];
   // Add this to make date_received nullable in the database
protected $dates = ['date_received'];
   public function user()
   {
       return $this->belongsTo(User::class, 'user_id');
   }
    public function totalRoyalty()
    {
        return $this->belongsTo(TotalAccumulatedRoyalty::class, 'user_id', 'user_id');
    }
}
