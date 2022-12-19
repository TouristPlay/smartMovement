<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message_id',
        'action',
        'to_stop_id',
        'from_stop_id',
    ];

    /**
     * @return BelongsToMany
     */
    public function user(): BelongsToMany
    {
        return $this->BelongsToMany(User::class);
    }
}
