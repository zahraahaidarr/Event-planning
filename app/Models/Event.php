<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $primaryKey = 'event_id';
    public $timestamps = false; // if not using created_at/updated_at

    protected $fillable = ['category_id','title','starts_at','ends_at', /* … */];

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id', 'category_id');
    }

    // D) per-event role lines
    public function workRoles()
    {
        return $this->hasMany(WorkRole::class, 'event_id', 'event_id');
    }

    // E) reservations
    public function reservations()
    {
        return $this->hasMany(WorkerReservation::class, 'event_id', 'event_id');
    }

    // F) post-event reports
    public function postEventSubmissions()
    {
        return $this->hasMany(PostEventSubmission::class, 'event_id', 'event_id');
    }
}
