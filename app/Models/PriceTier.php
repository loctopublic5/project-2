<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceTier extends Model
{
    protected $fillable = [
        'name',
        'slug', 
        'discount_percentage'
    ];
}
