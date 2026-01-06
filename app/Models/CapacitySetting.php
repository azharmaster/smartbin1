<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacitySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'empty_to',
        'half_to',
    ];
}

