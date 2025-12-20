<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     *
     * Make sure all these columns exist in your users table.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'date_of_birth', 
        'role',
        'status',
        'avatar_path',
        'date_of_birth',
    ];

    /**
     * Attributes that should be hidden.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth'     => 'date',
    ];

    /**
     * Accessors to append on array / JSON.
     */
    protected $appends = [
        'full_name',
    ];

    /**
     * Accessor: Full name (for 'full_name' attribute).
     */
    public function getFullNameAttribute(): string
    {
        $first    = trim($this->first_name ?? '');
        $last     = trim($this->last_name ?? '');
        $combined = trim($first . ' ' . $last);

        return $combined !== '' ? $combined : '';
    }

    /**
     * Accessor: Name (Laravel-style `$user->name`).
     * Uses the same logic; kept for compatibility.
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Relationships
     */

    // Employee profile (1–1)
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    // Worker profile (1–1)
    public function worker()
    {
        return $this->hasOne(Worker::class, 'user_id', 'id');
    }

    // Sent messages
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'id');
    }

    // Received messages
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'id');
    }

    // Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    // app/Models/User.php
public function followingEmployees()
{
    return $this->belongsToMany(
        \App\Models\User::class,
        'employee_follows',
        'follower_id',
        'followed_id'
    )->withTimestamps();
}


public function employeeFollowers()
{
  return $this->belongsToMany(User::class, 'employee_follows', 'followed_id', 'follower_id')
              ->withTimestamps();
}
}   