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
use App\Models\Cart;   // Nhớ import Cart
use App\Models\Role;   // Nhớ import Role
use App\Models\Review; // Nhớ import Review
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany

class User extends Authenticatable
{
    // Đã xóa Notifiable bị lặp
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, HasPermissions;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'avatar_url',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    // --- RELATIONSHIPS ---

    // 1. Roles (Thủ công - Chuẩn theo DB của bạn)
    public function roles(): BelongsToMany {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    // 2. Wallet
    public function wallet(): HasOne {
        return $this->hasOne(UserWallet::class);
    }

    // 3. Orders (SỬA: Đổi thành orders số nhiều và hasMany)
    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }

    // 4. Reviews
    public function reviews(): HasMany { // Đã thêm type hint HasMany
        return $this->hasMany(Review::class);
    }

    // 5. Voucher Usage
    public function voucherUsages(): HasMany { // Đã thêm type hint HasMany
        return $this->hasMany(VoucherUsage::class);
    }   

    // 6. Cart
    public function cart() {
        return $this->hasOne(Cart::class)->latestOfMany(); 
    }

    // 7. Addresses
    public function addresses(): HasMany { // Đã thêm type hint HasMany
        return $this->hasMany(UserAddress::class);
    }

    // --- HELPERS ---

    public function hasUsedVoucher($voucherId) {
        return $this->voucherUsages()->where('voucher_id', $voucherId)->exists();
    }

    public function defaultAddress() {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    /**
     * 1. Hàm kiểm tra User có role này không (Thay thế cho Spatie)
     * Cách dùng: $user->hasRole('admin') -> trả về true/false
     */
    public function hasRole($roleName)
    {
        // Nếu đã load quan hệ roles rồi thì check trong Collection (nhanh hơn)
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('name', $roleName);
        }
        
        // Nếu chưa load thì query database
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * 2. Hàm Scope để query nhanh (Dùng cho Analytics)
     * Cách dùng: User::role('customer')->count()
     */
    public function scopeRole($query, $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }
}