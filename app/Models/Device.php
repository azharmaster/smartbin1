<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sensor; // ✅ ADDED (required for latestSensor)

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'id_device',
        'device_name',
    ];

    // A Device belongs to an Asset
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

    public function latestSensor()
    {
        return $this->hasOne(
            Sensor::class,
            'device_id',
            'id_device'
        )->latestOfMany('time');
    }
}
