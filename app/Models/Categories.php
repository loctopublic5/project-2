<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categories extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'level',
        'is_active',
    ];

    public function products(): hasMany{
        return $this->hasMany('products', 'category_id', 'id' );
    } 
}
