<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacitySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'empty_to',
        'half_to',
    ];

    // Link to asset
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
