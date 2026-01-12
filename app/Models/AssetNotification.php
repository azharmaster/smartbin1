<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetNotification extends Model
{
    use HasFactory;

    protected $table = 'asset_notifications';

    protected $fillable = [
        'asset_id',
        'is_active',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function devices()
    {
        return $this->hasManyThrough(
            DeviceNotification::class,
            Device::class,
            'asset_id', // Foreign key on devices table
            'device_id', // Foreign key on device_notifications table
            'asset_id',  // Local key on assets table
            'id_device'  // Local key on devices table
        );
    }
}
