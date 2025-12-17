<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'title',
        'description',
        'assigned_to',
        'status',
    ];

    // A Sensor belongs to a Device
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
}
