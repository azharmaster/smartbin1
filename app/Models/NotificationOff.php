<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationOff extends Model
{
    use HasFactory;

    protected $table = 'notification_offs';

    protected $fillable = [
        'asset_id',   // ✅ Updated from bin_id to asset_id
        'start_at',
        'end_at',
        'created_by',
    ];

    /**
     * Relation to the bin/asset
     */
    public function bin()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'asset_id', 'id');
    }

    /**
     * Relation to the user who created the notification off
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
