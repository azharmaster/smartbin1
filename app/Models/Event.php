<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'pic_phone',
        'location',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
    ];

    /**
     * Cast date fields automatically to Carbon instances, formatted as Y-m-d
     */
    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
        'start_time' => 'string',
        'end_time'   => 'string',
    ];
}
