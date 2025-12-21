<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasPermissions, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'avatar_url',
        'is_active',
        'last_login_at',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function roles():  BelongsToMany{
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id','role_id');
    }

    public function dealerRequest(){
        return $this->hasMany(DealerRequest::class);
    }

    public function lastesrDealerRequest(){
        return $this->hasOne(DealerRequest::class)->latestOfMany();
    }

    /**
 * Kiểm tra user có role cụ thể nào đó không (dựa vào slug)
 * @param string $roleSlug (VD: 'admin')
 * @return bool
 */
    public function hasRole(string $roleSlug):bool {
        // Dùng collection method 'contains' để check trong danh sách roles đã eager load
         // Lưu ý: $this->roles là Collection (do Eloquent trả về)
        return $this->roles->contains('slug', $roleSlug);
    }
}
