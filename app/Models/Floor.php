<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Floor
 * 
 * @property int $id
 * @property string $floorName
 * @property string $picture
 *
 * @package App\Models
 */
class Floor extends Model
{
    //
    protected $table = 'floor';
	protected $primaryKey = 'id';

    protected $fillable = [
        'floor_name',
        'picture',
    ];

    // public function asset()
    // {
    //     return $this->hasMany(Asset::class);
    // }
}
