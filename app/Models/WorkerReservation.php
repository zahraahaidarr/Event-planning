<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerReservation extends Model
{
    use HasFactory;

    protected $table = 'workers_reservations';
    protected $primaryKey = 'reservation_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true; // keep as you defined

    protected $fillable = [
        'event_id',
        'work_role_id',
        'worker_id',
        'reserved_at',
        'status',
        'check_in_time',
        'check_out_time',
        'credited_hours',      // if exists
    ];

    protected $casts = [
        'hours' => 'decimal:2',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function workRole()
    {
        return $this->belongsTo(WorkRole::class, 'work_role_id', 'role_id');
    }
    public function role()
    {
        return $this->belongsTo(WorkRole::class, 'work_role_id');
    }
    public function postEventSubmissions()
{
    return $this->hasMany(PostEventSubmission::class, 'worker_reservation_id');
}

}
