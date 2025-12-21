<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePost extends Model
{
    protected $table = 'employee_posts';

    protected $fillable = [
        'employee_user_id', 'title', 'content', 'media_path'
    ];
}
