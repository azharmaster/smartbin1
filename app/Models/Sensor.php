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
}
