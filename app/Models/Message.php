<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $primaryKey = 'message_id';
    public $timestamps = false; // you have a single 'timestamp' column

    protected $fillable = ['sender_id','receiver_id','content','timestamp','is_read'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id')->withDefault();
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id')->withDefault();
    }
}
