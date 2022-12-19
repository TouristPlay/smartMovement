<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;


    protected $fillable = [
        'transport_id',
        'stop_id',
        'parent_id'
    ];
}
