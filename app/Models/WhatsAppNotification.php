<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppNotification extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_notifications';

    protected $fillable = [
        'title',
        'message',
        'is_active',
        'start_time',
        'end_time',
        'last_sent_at',
    ];

    // Cast timestamps to Carbon instances
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'last_sent_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
