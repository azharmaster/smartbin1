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

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    protected $dates = ['sent_at'];

    public function asset()
{
    return $this->belongsTo(Asset::class, 'asset_id', 'id');
}

// In NotificationLog model (ni dgn atas maybe bole buang)
public function device() {
    return $this->belongsTo(Device::class, 'id_device', 'id_device');
}
}
