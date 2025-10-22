<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WorkerSkill extends Pivot
{
    protected $table = 'workers_skills';
    public $timestamps = false;
    protected $fillable = ['worker_id','skill_id'];
}
