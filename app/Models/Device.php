<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sensor; // ✅ ADDED (required for latestSensor)
use App\Models\Asset;  // ✅ ADDED to link Device to Asset

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'id_device',
        'device_name',
        'is_active', // ✅ Added for device-level ON/OFF
    ];

    protected $casts = [
        'is_active' => 'boolean', // Cast to boolean for Blade ON/OFF switch
    ];

    // A Device belongs to an Asset (Bin)
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    // A Device has many Sensors
    public function sensors()
    {
        return $this->hasMany(
            Sensor::class,
            'device_id',
            'id_device'
        );
    }

    /**
     * Get the latest Sensor reading for this Device
     * This allows $device->latestSensor to get the most recent sensor data
     */
    public function latestSensor()
    {
        return $this->hasOne(
            Sensor::class,
            'device_id',
            'id_device'
        )->latestOfMany('time');
    }
}
