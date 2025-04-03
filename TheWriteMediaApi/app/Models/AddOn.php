<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class AddOn extends Model
{
    protected $connection = 'mongodb';  
    protected $collection = 'add_ons'; 
    protected $fillable = [
        'name',
        'rows', // This will store the table rows as an array of arrays
    ];

    protected $casts = [
        'rows' => 'array', // Cast rows to an array
    ];

}
