<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceNotification extends Model
{
    use HasFactory;

    protected $table = 'device_notifications';

    protected $fillable = [
        'device_id',
        'is_active',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'id_device');
    }

    public function asset()
    {
        return $this->device->asset();
    }
}
