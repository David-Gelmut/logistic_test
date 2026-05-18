<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'request_id',
        'user_id',
        'channel',
        'message',
        'contact',
        'status',
        'priority',
        'error_details',
        'retry_count'
    ];
}
