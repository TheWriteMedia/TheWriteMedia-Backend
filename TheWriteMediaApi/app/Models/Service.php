<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';  
    protected $collection = 'services'; 

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type', // 'marketing' or 'publishing'
        'imageUrl',
        'additional_info', // For marketing services (array of strings)
        'inclusions_table', // Add this field
        'status'
    ];

    protected $casts = [
        'additional_info' => 'array', // Cast additional_info to an array
        'inclusions_table' => 'array', // Cast inclusions_table to an array
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically set 'status' to 'ACTIVE' when creating a service record
        static::creating(function ($service) {
            if (!$service->status) {
                $service->status = 'ACTIVE';
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
