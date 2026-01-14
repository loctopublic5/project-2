<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'role_id',
        'resource',
        'action',
    ];

    public function role(): BelongsToMany{
        return $this->belongsToMany(Role::class, 'permissions', 'role_id', 'id');
    }
}
