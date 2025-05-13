<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
      /** @use HasFactory<\Database\Factories\TotalAccumulatedRoyaltyFactory> */
      use HasFactory;

    
      protected $connection = 'mongodb';  
      protected $collection = 'orders'; 
  
      protected $fillable = [
        'email_address',
        'contactno',
        'items',
        'country',
        'address_line_one',
        'address_line_two',
        'province',
        'city',
        'postal_code',
        'total',
        'shipping_fee',
        'status'

        
    ];

    protected $attributes = [
        'status' => 'PENDING',
        'shipping_fee' => 0
    ];
}
