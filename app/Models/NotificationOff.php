<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationOff extends Model
{
    use HasFactory;

    protected $fillable = ['asset_id', 'device_id', 'start_at', 'end_at', 'active'];

    public function asset() {
        return $this->belongsTo(Asset::class);
    }

    public function device() {
        return $this->belongsTo(Device::class);
    }
}
