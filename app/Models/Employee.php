<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Table name (optional, Laravel will guess 'employees')
    protected $table = 'employees';

    // Primary key matches your migration
    protected $primaryKey = 'employee_id';

    // Your table *does* include timestamps, so keep this true
    public $timestamps = true;

    // Allow mass assignment
    protected $fillable = [
        'user_id',
        'position',
        'department',
        'hire_date',
        'is_active',
    ];

    /**
     * Each employee belongs to one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * If you later use announcements (posted_by = employee_id).
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'posted_by', 'employee_id');
    }
}
