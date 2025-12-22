<?php

namespace App\Services\System;

class PricingService
{
// public function calculatePrice($product, $user){

//     // BƯỚC 1: KHỞI TẠO GIÁ MẶC ĐỊNH
//     // Mặc định giá cuối cùng bằng giá niêm yết
//     $finalPrice = $product.price;
//     $appliedRule = "none";

//     // BƯỚC 2: TÌM "GIÁ PUBLIC" TỐT NHẤT (Public Candidate)
//     // Kiểm tra xem sản phẩm có đang Sale đại trà không?
//     $publicSalePrice = $product.price; // Mặc định là giá gốc
    
//     if ($product.sale_price > 0 AND $product.sale_price < $product.price) {
//         $publicSalePrice = $product.sale_price;
//     }

//     // BƯỚC 3: TÌM "GIÁ DEALER" TỐT NHẤT (Dealer Candidate)
//     $dealerPrice = $product.price; // Mặc định cũng là giá gốc

//     if ($user !== NULL && $user.isDealer()) {
//         $tier = $user.dealerProfile.tier;
//         // Lấy thông tin cấp bậc đại lý
        
        
//         IF ($tier !== NULL) {
//             // Công thức: Giá gốc * (100% - %giảm)
//             $discountRate = $tier.discount_percentage;
//             $dealerPrice = $product.price * (1.0 - discountRate);
//     }
// }

//     // BƯỚC 4: CHIẾN LƯỢC "BEST PRICE WINS" (SO SÁNH)
//     // So sánh 2 ứng cử viên: publicSalePrice vs dealerPrice
    
//     IF (dealerPrice < publicSalePrice) THEN
//         // Đại lý rẻ hơn -> Chọn Dealer Price
//         finalPrice = dealerPrice
//         appliedRule = "dealer_tier_" + tier.name
//     ELSE IF (publicSalePrice < product.price) THEN
//         // Sale đại trà rẻ hơn (hoặc bằng) -> Chọn Public Price
//         finalPrice = publicSalePrice
//         appliedRule = "public_sale"
//     ELSE
//         // Không có khuyến mãi nào -> Về giá gốc
//         finalPrice = product.price
//         appliedRule = "none"
//     END IF

//     // BƯỚC 5: TRẢ VỀ KẾT QUẢ (Object)
//     RETURN {
//         "original_price": product.price,
//         "final_price": finalPrice,
//         "discount_amount": product.price - finalPrice,
//         "applied_rule": appliedRule
//     }

}
?>
