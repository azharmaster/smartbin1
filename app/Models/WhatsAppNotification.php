<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WhatsAppNotification extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_notifications';

    protected $fillable = [
        'title',
        'message',
        'is_active',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'last_sent_at',
    ];

    // Cast timestamps to Carbon instances
    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'start_time'   => 'string', // 'time' type, keep as string
        'end_time'     => 'string', // 'time' type, keep as string
        'last_sent_at' => 'datetime',
        'is_active'    => 'boolean',
    ];

    /**
     * Ensure start_time is always returned as 'H:i' format
     */
    public function getStartTimeAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

    /**
     * Ensure end_time is always returned as 'H:i' format
     */
    public function getEndTimeAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }
}
