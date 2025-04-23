<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
class Notification extends Model
{

    /** @use HasFactory<\Database\Factories\MarkFactory> */
    use HasFactory;
    protected $connection = 'mongodb'; // Use MongoDB connection
    protected $collection = 'notifications'; // Specify collection name

    protected $fillable = [
        'title',
        'message',
        'user_id', // The user this notification belongs to
        'is_read', // Whether the user has read the notification
        'type',       // Add this
    'reference_id' // Add this (optional for linking to specific entities)
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}