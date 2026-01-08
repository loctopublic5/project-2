<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Order;
use App\Models\UserWallet;
use App\Models\UserAddress;
use App\Models\VoucherUsage;
use App\Traits\HasPermissions;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasPermissions, HasApiTokens,Notifiable;

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

    public function wallet(): HasOne{
        return $this->hasOne(UserWallet::class);
    }

    public function order(): HasOne{
        return $this->hasOne(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function voucherUsages()
    {
        return $this->hasMany(VoucherUsage::class);
    }   

    // Lấy giỏ hàng hiện tại của User
    // Dùng hasOne vì tại 1 thời điểm, 1 user chỉ active 1 giỏ hàng (giỏ cũ nhất hoặc mới nhất)
    public function cart()
    {
        return $this->hasOne(Cart::class)->latestOfMany(); 
    }

    // Helper function tiện lợi
    public function hasUsedVoucher($voucherId)
    {
        // Kiểm tra nhanh xem user đã dùng voucher này chưa
        return $this->voucherUsages()->where('voucher_id', $voucherId)->exists();
    }

    // 1. Lấy toàn bộ danh sách địa chỉ của User
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    // 2. Lấy địa chỉ mặc định (Logic "One Default")
    // Helper cực tiện lợi để gọi: $user->defaultAddress
    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }
}