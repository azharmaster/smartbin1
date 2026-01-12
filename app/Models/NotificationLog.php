<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'channel',
        'message_preview',
        'message_full',
        'sent_at',
    ];

    protected $dates = ['sent_at'];
}
