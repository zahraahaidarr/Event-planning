<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';
    protected $primaryKey = 'announcement_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Your table has created_at but no updated_at
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'title', 'body', 'posted_by', 'audience', 'created_at', 'expires_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
    
}
