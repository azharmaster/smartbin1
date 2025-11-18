<?php

namespace App\Models;

use DateTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Asset
 * 
 * @property int $id
 * @property string $asset_name
 * @property int $floor_id
 * @property string $serialNo
 * @property string $description
 * @property string $model
 * @property Carbon $maintenance
 * @property string $category
 *
 * @package App\Models
 */
class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use HasFactory;

    protected $table = 'assets';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'asset_name',
        'floor_id',
        'serialNo',
        'description',
        'model',
        'maintenance',
        'category',
        'timestamps',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id', 'id');
    }
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}

