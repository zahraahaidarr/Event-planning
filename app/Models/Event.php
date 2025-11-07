<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $primaryKey = 'event_id';
    public $incrementing = true;
    public $timestamps = true; // you have created_at / updated_at

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'location',
        'venue_area_sqm',
        'expected_attendance',
        'total_spots',
        'status',
        'requirements',
        'starts_at',
        'ends_at',
        'duration_hours',
        'created_by',
        // 'created_by', // add only if nullable / you want to fill it
    ];

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id', 'category_id');
    }

    public function workRoles()
    {
        return $this->hasMany(WorkRole::class, 'event_id', 'event_id');
    }

    public function reservations()
    {
        return $this->hasMany(WorkerReservation::class, 'event_id', 'event_id');
    }

    public function postEventSubmissions()
    {
        return $this->hasMany(PostEventSubmission::class, 'event_id', 'event_id');
    }
}
