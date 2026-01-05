<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserAddress extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone',
        'province_id',
        'district_id',
        'ward_id',
        'address_detail',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function users(): BelongsToMany{
        return $this->belongsToMany(User::class);
    }
}
