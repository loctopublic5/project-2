<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\Log;

class PricingService{

    /**
     * Hàm tính toán tổng hợp
     * @param array $cartItems: Danh sách SP và số lượng
     * @param string|null $voucherCode: Mã giảm giá user nhập
     * @return array: Cấu trúc giá chi tiết
     */
    public function calculateCart($cartItems, $voucherCode = NULL){

    // --- BƯỚC A: TÍNH TỔNG TIỀN HÀNG (SUBTOTAL) ---
    $subtotal = 0;
    $lineItemsSnapshot = []; // Mảng lưu chi tiết để insert vào DB sau này

    foreach ($cartItems as $item){
        // 1. Lấy ID sản phẩm từ item hiện tại
        $productId = $item['product_id'];

        // Lấy thông tin sản phẩm từ DB
        $product = Product::find($productId);
        if (!$product) continue; // Nếu không tìm thấy sản phẩm thì bỏ qua

        // LOGIC CHỌN GIÁ (B2C Pricing Rule)
        // Ưu tiên sale_price nếu có giá trị và > 0
        if($product->sale_price !== null && $product->sale_price > 0){
            $finalUnitPrice = $product->sale_price;
        } else {
            $finalUnitPrice = $product->price;
        }

        // Tính thành tiền
        $lineTotal = $finalUnitPrice * $item['quantity'];
        $subtotal += $lineTotal;

        // TẠO SNAPSHOT (Quan trọng cho bảng order_items)
        // Lưu lại chính xác tên và giá tại thời điểm này
        $lineItemsSnapshot[] = [
            'product_id'        => $product->id,
            'quantity'          => $item['quantity'],
            'price_at_purchase' => $finalUnitPrice,            // Lưu giá chốt
            'product_name'      => $product->name,           // Lưu tên chốt
            'variant_snapshot'  => $item['options'] ?? null,   // (Nếu có color/size)
        ];
    }

    // --- BƯỚC B: TÍNH GIẢM GIÁ (VOUCHER) ---
    $discountAmount = 0;
    $voucherInfo = NULL;

    if ($voucherCode !== null){
        // Tìm voucher trong DB
        $voucher = Voucher::where('code', $voucherCode)->first();

        if ($voucher){
            try{
                $this->validateVoucher($voucher, $subtotal);

                // Tính toán tiền giảm dựa trên loại
                if($voucher->type === 'percent'){
                    $discountAmount = $subtotal * ($voucher->value / 100);
                } else if ($voucher->type == 'fixed'){
                    $discountAmount = $voucher->value;
                }
                
                // Lưu lại thông tin voucher để trả về FE
                $voucherInfo =[
                    'code'           => $voucher->code,
                    'discount_value' => $discountAmount,
                    'type'           => $voucher->type,
                ];
            } catch (Exception $e){
                $discountAmount = 0;
                $voucherInfo = null;
                Log::warning("Voucher Error: " . $e->getMessage());
            }
        }
    }

    // --- BƯỚC C: TỔNG KẾT & PHÍ SHIP ---
    // Giả định phí ship tạm thời (Sẽ tính bằng ShippingService sau)
    $shippingFee = 30000; 

    // Logic an toàn: Đảm bảo (Hàng - Voucher) không bao giờ ÂM
    // Hàm MAX(0, value) rất quan trọng
    $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);

    // Tổng thanh toán cuối cùng = (Hàng sau giảm) + Ship
    $finalTotal = $subtotalAfterDiscount + $shippingFee;

    // --- TRẢ VỀ KẾT QUẢ (DATA STRUCTURE) ---
    return  [
        'subtotal'        => $subtotal,        // Tổng tiền hàng gốc
        'discount_amount' => $discountAmount, // Tiền được giảm
        'shipping_fee'    => $shippingFee,    // Phí ship
        'total'           => $finalTotal,            // Con số khách phải trả (trừ vào Ví)
        'items'           => $lineItemsSnapshot,     // Dữ liệu để insert vào order_items
        'voucher'         => $voucherInfo          // Thông tin voucher áp dụng
    ];
    }

    /**
     * Hàm kiểm tra Voucher (Re-usable)
     */
    private function validateVoucher($voucher, $orderSubtotal){
    // 1. Check số lượng (Inventory)
    if ($voucher->quantity <= 0){
        throw new Exception("Mã giảm giá này đã hết lượt sử dụng.");
    }

    // 2. Check thời gian (Real-time)
    // Quan trọng: Phải so sánh với thời điểm hiện tại (NOW)
    if (now()->lt($voucher->start_date)) {
        throw new Exception("Mã giảm giá chưa đến đợt áp dụng.");
    }
    
    if (now()->gt($voucher->end_date)){
        throw new Exception("Mã giảm giá đã hết hạn.");
    }

    // 3. Check điều kiện đơn hàng (Minimum Spend)
    // Logic B2C: Mua 500k mới được giảm 50k
    if ($orderSubtotal < $voucher->min_order_value){
        throw new Exception("Đơn hàng chưa đạt giá trị tối thiểu: ". number_format($voucher->min_order_value));
    }

    return True; // Hợp lệ
    }
}