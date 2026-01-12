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
 * @property string $location
 * @property string $model
 * @property Carbon $maintenance
 * @property string $picture
 * @property decimal $x
 * @property decimal $y
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
        'asset_name',
        'floor_id',
        'serialNo',
        'location',
        'model',
        'maintenance',
        'picture',
        'x',
        'y',
        'is_active', // Added for notification ON/OFF
    ];

    protected $casts = [
        'is_active' => 'boolean', // Cast to boolean for convenience
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id', 'id');
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function complaint()
    {
        return $this->hasMany(Complaint::class);
    }
}
