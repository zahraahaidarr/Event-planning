<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeStory extends Model
{
    protected $table = 'employee_stories';

    protected $fillable = [
        'employee_user_id', 'media_path', 'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function employeeUser()
{
    return $this->belongsTo(\App\Models\User::class, 'employee_user_id');
}

public function views()
{
    return $this->hasMany(\App\Models\EmployeeStoryView::class, 'employee_story_id');
}

}
