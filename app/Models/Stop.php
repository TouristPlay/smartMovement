<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Stop extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'stop_id',
        'address',
        'url',
        'latitude',
        'longitude',
    ];


    /**
     * @return HasOne
     */
    public function city(): HasOne
    {
        return $this->hasOne(City::class);
    }

    /**
     * @return BelongsToMany
     */
    public function favorite() : BelongsToMany
    {
        return $this->BelongsToMany(FavoriteStop::class);
    }
}
