<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transport extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'slug',
        'type',
        'city_id'
    ];


    /**
     * @return HasMany
     */
    public function city(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
