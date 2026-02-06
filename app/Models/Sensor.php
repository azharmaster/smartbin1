<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'battery',
        'capacity',
        'time',
        'rsrp',
        'nsr',
    ];

    // A Sensor belongs to a Device
    public function device()
    {
        return $this->belongsTo(
            Device::class,
            'device_id',
            'id_device'
        );
    }

    /**
     * Access the Asset (bin) through the Device
     * This allows $sensor->asset to get the bin directly
     */
    public function asset()
    {
        return $this->device ? $this->device->asset : null;
    }
    
    /**
     * Calculate battery percentage from voltage
     * 
     * @return int Battery percentage based on voltage
     */
    public function getBatteryPercentageAttribute()
    {
        $voltage = $this->battery;
        
        if ($voltage === null) {
            return 0;
        }
        
        // Voltage to percentage mapping
        // Conditions are evaluated in order, so once a condition is met,
        // the rest are not checked
        if ($voltage >= 3.7) {
            return 100;
        } elseif ($voltage >= 3.6) {
            return 98;
        } elseif ($voltage >= 3.5) {
            return 95;
        } elseif ($voltage >= 3.4) {
            return 80;
        } elseif ($voltage >= 3.3) {
            return 20;
        } elseif ($voltage >= 3.2) {
            return 10;
        } elseif ($voltage >= 3.1) {
            return 8;
        } elseif ($voltage >= 3.0) {
            return 5;
        } elseif ($voltage >= 2.9) {
            return 3;
        } elseif ($voltage >= 2.8) {
            return 1;
        } else {
            return 0;
        }
    }
}
