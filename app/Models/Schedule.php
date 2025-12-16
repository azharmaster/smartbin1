<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
    'user_id',
    'floor_id',
    'start_shift',
    'end_shift',
    'date',
];


    // Relationship: Schedule belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Schedule belongs to a Floor
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
