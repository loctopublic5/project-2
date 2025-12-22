<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;

class PricingService
{
    /**
     * Tính toán giá sản phẩm dựa trên User Context
     * Chiến lược: Best Price Wins (Giá nào rẻ nhất thì lấy)
     * * @param Product $product
     * @param User|null $user (Có thể null nếu là khách vãng lai)
     * @return array Cấu trúc giá chi tiết
     */
    public function calculatePrice(Product $product, ?User $user = null): array
    {
        // --- BƯỚC 1: XÁC ĐỊNH GIÁ GỐC (BASE) ---
        $originalPrice = $product->price;
        
        // Mặc định: Giá cuối cùng = Giá gốc (Chưa giảm)
        $finalPrice = $originalPrice;
        $appliedRule = 'none'; // Lý do giảm giá: none, public_sale, dealer_tier...


        // --- BƯỚC 2: TÌM GIÁ SALE ĐẠI TRÀ (PUBLIC CANDIDATE) ---
        // TODO: Kiểm tra xem sản phẩm có giá sale hợp lệ không?
        // Logic: sale_price phải > 0 VÀ sale_price phải nhỏ hơn originalPrice.
        // Gợi ý: Dùng toán tử 3 ngôi hoặc if.
        
        // Biến tạm để so sánh
        $publicPrice = $originalPrice; 
        
        // Viết logic cập nhật $publicPrice tại đây:
        if ($product->sale_price > 0 && $product->sale_price < $publicPrice) 
            { $publicPrice = $product->sale_price; };


        // --- BƯỚC 3: TÌM GIÁ ĐẠI LÝ (DEALER CANDIDATE) ---
        // Mặc định giá đại lý bằng giá gốc (nếu không phải đại lý)
        $dealerPrice = $originalPrice; 
        $tierName = null;

        // TODO: Kiểm tra User có tồn tại KHÔNG? VÀ User có phải Dealer KHÔNG?
        // Gợi ý: check $user && $user->dealerProfile && $user->dealerProfile->tier
        if ($user && $user->dealerProfile) { 
            $tier = $user->dealerProfile->tier;
            
            if ($tier) {
                $tierName = $tier->name;
                $percent = $tier->discount_percentage;
                
                // Công thức chuẩn: Giá gốc * (1 - %/100)
                $dealerPrice = round($originalPrice * (100 - $percent) / 100);
            }
        }
        


        // --- BƯỚC 4: CHIẾN LƯỢC "BEST PRICE WINS" (SO SÁNH & CHỐT ĐƠN) ---
        
        // Case A: Giá đại lý rẻ hơn (hoặc bằng) giá Public Sale
        // Điều kiện: $dealerPrice < $publicPrice
        if ($dealerPrice < $publicPrice) {
            $finalPrice = $dealerPrice;
            $appliedRule = 'dealer_tier_' . ($tierName ?? 'unknown');
        } 
        // Case B: Giá Public Sale rẻ hơn
        // Điều kiện: $publicPrice < $originalPrice
        elseif ($publicPrice < $originalPrice) {
            $finalPrice = $publicPrice;
            $appliedRule = 'public_sale';
        }
        // Case C: Không có giảm giá nào (Giữ nguyên giá gốc)
        else {
            $finalPrice = $originalPrice;
            $appliedRule = 'none';
        }

        // --- BƯỚC 5: TRẢ VỀ KẾT QUẢ ---
        return [
            'original_price'   => $originalPrice,
            'final_price'      => $finalPrice,
            'discount_amount'  => $originalPrice - $finalPrice,
            'discount_percent' => $originalPrice > 0 ? round((($originalPrice - $finalPrice) / $originalPrice) * 100, 2) : 0,
            'applied_rule'     => $appliedRule,
        ];
    }
}