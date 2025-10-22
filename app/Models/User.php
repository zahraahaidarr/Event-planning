<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password','phone','role','status',];

    protected $hidden   = ['password','remember_token'];

    // A) People & Profiles
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id'); // 1–1
    }

    public function worker()
    {
        return $this->hasOne(Worker::class, 'user_id', 'id');   // 1–1
    }

    // G) Messaging
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'id');
    }

    // H) Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }
}
