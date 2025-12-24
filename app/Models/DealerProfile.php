<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerProfile extends Model
{
    protected $fillable = [
        'user_id', 
        'price_tier_id', 
        'brand_name', 
        'tax_id', 
        'address', 
        'phone_business'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tier()
    {
        // Liên kết sang bảng price_tiers
        return $this->belongsTo(PriceTier::class, 'price_tier_id');
    }

}
