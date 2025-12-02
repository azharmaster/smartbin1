<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveQuota extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'year',
        'annual_leave',      // Annual leave days
        'mc',                // MC days
        'hospitality',       // Hospitality leave days
        'emergency_leave',   // Emergency leave days
        'used_days',         // Total used days
    ];

    /**
     * Relationship: the user this quota belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Total allocated days (sum of all leave types)
     */
    public function getTotalDaysAttribute()
    {
        return ($this->annual_leave ?? 0) 
             + ($this->mc ?? 0) 
             + ($this->hospitality ?? 0) 
             + ($this->emergency_leave ?? 0);
    }

    /**
     * Remaining days = total allocated - used
     */
    public function getRemainingDaysAttribute()
    {
        return max(0, $this->total_days - ($this->used_days ?? 0));
    }
}
