<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'type',       // Full Day / Half Day
        'start_date',
        'end_date',
        'reason',
        'use',        // MC / Annual Leave / Emergency Leave / Hospitality
        'status',     // Pending / Approved / Rejected
    ];

    /**
     * The user who submitted the leave.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for leave duration in days.
     */
    public function getDurationAttribute()
    {
        if (!$this->end_date) {
            return 1; // Default 1 day if end_date not set
        }
        return (strtotime($this->end_date) - strtotime($this->start_date)) / 86400 + 1;
    }
}
