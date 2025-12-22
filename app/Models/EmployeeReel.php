<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeReel extends Model
{
    protected $table = 'employee_reels';

    protected $fillable = [
        'employee_user_id', 'video_path', 'caption'
    ];

    public function likes()
{
    return $this->morphMany(\App\Models\Like::class, 'likeable');
}

public function comments()
{
    return $this->morphMany(\App\Models\Comment::class, 'commentable')->latest();
}

}
