<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

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
     * Получаем остановки в радиусе
     * @param $latitude
     * @param $longitude
     * @param $distance
     * @return mixed
     */
    public static function getCordBetweenDistance($latitude, $longitude, $distance)
    {
        return self::select(
            ['*',
                DB::raw(
                    "(6371 * acos(cos(radians(" . $latitude . ")) 
                    * cos(radians(`latitude`)) 
                    * cos(radians(`longitude`) 
                    - radians(" . $longitude . ")) 
                    + sin(radians(" . $latitude . ")) 
                    * sin(radians(`latitude`)))) as distance")])
            ->havingRaw('distance <= ' . $distance)
            ->get();
    }


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

    /**
     * @return HasMany
     */
    public function route(): HasMany
    {
        return $this->hasMany(Route::class);
    }
}
