<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeStoryView extends Model
{
    protected $fillable = ['viewer_user_id','employee_story_id','seen_at'];

    public function story()
    {
        return $this->belongsTo(EmployeeStory::class, 'employee_story_id');
    }

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewer_user_id');
    }
}
