<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkRole extends Model
{
    use HasFactory;

    protected $primaryKey = 'role_id';
    public $timestamps = false;

    protected $fillable = ['event_id','role_type_id','required_spots', /* … */];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function roleType()
    {
        return $this->belongsTo(RoleType::class, 'role_type_id', 'role_type_id');
    }

    public function reservations()
    {
        return $this->hasMany(WorkerReservation::class, 'work_role_id', 'role_id');
    }

    public function workers()
{
    return $this->hasManyThrough(
        Worker::class,
        WorkerReservation::class,
        'work_role_id', // FK on WorkerReservation
        'worker_id',    // FK on Worker
        'role_id',      // Local key on WorkRole
        'worker_id'     // Local key on WorkerReservation
    );
}

}
