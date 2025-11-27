<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $fillable = ['userID', 'todo', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'userID');
    }
}
