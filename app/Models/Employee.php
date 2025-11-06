<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'employees';

    // Primary key (as seen in phpMyAdmin)
    protected $primaryKey = 'employee_id';
    
    // Timestamps are present in your table
    public $timestamps = true;

    // Fields that can be mass-assigned
    protected $fillable = [
        'user_id',
        'position',
        'department',
        'hire_date',
        'is_active',
    ];

    // Automatically cast types for convenience
    protected $casts = [
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relation: each employee belongs to one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * (Optional) Relation if you later link announcements.
     * Remove if not used yet.
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'posted_by', 'employee_id');
    }
}
