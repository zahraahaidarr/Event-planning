<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePost extends Model
{
    protected $table = 'employee_posts';

    protected $fillable = [
        'employee_user_id', 'title', 'content', 'media_path'
    ];


    public function likes()
{
    return $this->morphMany(\App\Models\Like::class, 'likeable');
}

public function comments()
{
    return $this->morphMany(\App\Models\Comment::class, 'commentable')->latest();
}
    public function event()
    {
        // employee_posts.event_id  -> events.event_id
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }


}
