<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $guarded =['id'];

     protected $fillable = [
        'nama_kategori'
    ];

    //   public function products()
    // {
    //     return $this->hasMany(Product::class, 'kategori_id');
    // }
}
