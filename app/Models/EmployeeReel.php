<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeReel extends Model
{
    protected $table = 'employee_reels';

    protected $fillable = [
        'employee_user_id', 'video_path', 'caption'
    ];
}
