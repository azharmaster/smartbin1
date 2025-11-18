<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
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
        return $this->hasMany(Sensor::class);
    }

    // Optional: Get the latest sensor
    public function latestSensor()
    {
        return $this->hasOne(Sensor::class)->latestOfMany();
    }
}
