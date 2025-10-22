<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostEventSubmission extends Model
{
    use HasFactory;

    protected $primaryKey = 'submission_id';
    public $timestamps = false;

    protected $fillable = ['event_id','worker_id','content', /* … */];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }
}
