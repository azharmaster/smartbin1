<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Updated fillable columns
    protected $fillable = [
        'asset_id',
        'user_id',
        'floor_id',
        'description',
        'notes',
        'status',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to Asset
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    // Relationship to Floor
    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }
}
