<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerReservation extends Model
{
    use HasFactory;

    protected $table = 'workers_reservations';
    protected $primaryKey = 'reservation_id';
    public $timestamps = false;

    protected $fillable = ['worker_id','event_id','work_role_id','status', /* … */];

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

    protected $casts = [
    'hours' => 'decimal:2',
];

}
