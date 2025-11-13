<?php

// app/Models/Notification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table      = 'notifications';
    protected $primaryKey = 'notification_id';
    public $timestamps    = false; // your table has only created_at

    protected $fillable = [
        'user_id', 'title', 'message', 'type', 'is_read', 'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

