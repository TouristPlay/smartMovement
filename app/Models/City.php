<?php

namespace App\Models;

use App\Models\Stop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'slug',
        'city_id',
        'latitude',
        'longitude'
    ];

    /**
     * @return BelongsTo
     */
    public function stop() : belongsTo {
        return $this->belongsTo(Stop::class);
    }

    public function transport() : belongsTo {
        return $this->belongsTo(Transport::class);
    }
}
