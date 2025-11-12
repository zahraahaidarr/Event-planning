<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkRole extends Model
{
    use HasFactory;

    protected $table = 'work_roles';
    protected $primaryKey = 'role_id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'event_id',
        'role_type_id',
        'role_name',
        'required_spots',
        'calc_source',
        'calc_confidence',
        'description',
    ];

    /* ---------- Relationships ---------- */

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
        // FK on workers_reservations is work_role_id
        return $this->hasMany(WorkerReservation::class, 'work_role_id', 'role_id');
    }

    public function workers()
    {
        // all workers who reserved this specific work_role
        return $this->hasManyThrough(
            Worker::class,              // final
            WorkerReservation::class,   // through
            'work_role_id',             // FK on WorkerReservation → WorkRole
            'worker_id',                // FK on Worker → workers table
            'role_id',                  // local key on WorkRole
            'worker_id'                 // local key on WorkerReservation
        );
    }
}
