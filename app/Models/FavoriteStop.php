<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FavoriteStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stop_id'
    ];

    /**
     * @return HasOne
     */
    public function stop(): HasOne
    {
        return $this->hasOne(Stop::class);
    }

    /**
     * @return belongsTo
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
