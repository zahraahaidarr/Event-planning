<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $primaryKey = 'skill_id';
    public $timestamps = false;

    protected $fillable = ['name','description'];

    public function workers()
    {
        return $this->belongsToMany(
            Worker::class,
            'workers_skills',
            'skill_id',
            'worker_id'
        )->withTimestamps();
    }
}
