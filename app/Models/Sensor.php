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
        'network',
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
}
