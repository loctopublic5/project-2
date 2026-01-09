<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function users(): BelongsToMany{
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    public function permissions()
    {
    // Quan hệ Many-to-Many chuẩn Laravel
    // Tham số thứ 2 là tên bảng trung gian: 'permission_role'
    return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }
}
