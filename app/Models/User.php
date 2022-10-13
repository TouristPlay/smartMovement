<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
      'username',
      'chat_id',
      'first_name',
      'last_name',
    ];

    /**
     * @return belongsTo
     */
    public function message(): belongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * @return hasMany
     */
    public function favorite(): hasMany
    {
        return $this->hasMany(FavoriteStop::class);
    }
}
