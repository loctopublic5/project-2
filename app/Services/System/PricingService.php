<?php

namespace App\Services\System;

use Exception;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\Log;
use App\Services\Order\AddressService;
use App\Services\Order\VoucherService;
use App\Services\Order\ShippingService;

class PricingService{

    protected $voucherService;
    protected $shippingService;
    protected $addressService;

    public function __construct(
        VoucherService $voucherService,
        ShippingService $shippingService,
        AddressService $addressService
    ) {
        $this->voucherService = $voucherService;
        $this->shippingService = $shippingService;
        $this->addressService = $addressService;
    }

    /**
     * Hàm tính toán tổng hợp
     * @param array $cartItems: Danh sách SP và số lượng
     * @param string|null $voucherCode: Mã giảm giá user nhập
     * @return array: Cấu trúc giá chi tiết
     */
    public function calculateCart(
        array $cartItems, 
        ?string $voucherCode = null, 
        ?int $userId = null,
        ?int $addressId = null
    ): array{

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

    if ($voucherCode){
        try{
                $voucher = $this->voucherService->validateVoucher($voucherCode, $subtotal, $userId);

                $discountAmount = $this->calculateDiscountAmount($voucher, $subtotal);

                // Lưu lại thông tin voucher để trả về FE
                $voucherInfo = [
                    'id'             => $voucher->id,
                    'code'           => $voucher->code,
                    'type'           => $voucher->type,
                    'discount_value' => $discountAmount,
            ];
        } catch (Exception $e){
            $discountAmount = 0;
            $voucherInfo = null;
            Log::warning("Voucher Error: " . $e->getMessage());
        }
    }
    

    // --- BƯỚC C: TÍNH SHIP (INTEGRATION LOGIC) ---
    $shippingFee = 0; 

    if($userId && $addressId){
        // 1. Gọi AddressService lấy thông tin địa chỉ thật từ DB
        // (Hàm getAddressDetail đã có check user_id bên trong -> An toàn)
        try{
            $address = $this->addressService->getAddressDetail($userId,$addressId);
            // 2. [QUAN TRỌNG] Convert ID sang Tên Tỉnh
            // Lý do: ShippingService cần string để check slug
            $cityName = $this->resolveProvinceName($address->province_id);
            // 3. Gọi Shipping Service
            // ShippingService của bạn nhận mảng ['city' => '...'] hoặc string
            $shippingFee = $this->shippingService->calculateShippingFee([
                'city' => $cityName
            ]);
        }catch(Exception $e){
            $shippingFee = 0;
            throw $e;
        }
    }
    
    // --- BƯỚC D: TỔNG KẾT ---
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


    // --- HELPER METHODS ---

    private function calculateDiscountAmount($voucher, float $subtotal): float
    {
        $discount = 0;
        if ($voucher->type === 'fixed') {
            $discount = $voucher->value;
        } elseif ($voucher->type === 'percent') {
            $discount = $subtotal * ($voucher->value / 100);
            if ($voucher->max_discount_amount) {
                $discount = min($discount, $voucher->max_discount_amount);
            }
        }
        return min($discount, $subtotal);
    }
    /**
     * Helper tạm thời để map ID sang Tên
     * TODO: Sau này nên có bảng 'provinces' trong DB và gọi $address->province->name
     */
    private function resolveProvinceName($provinceId)
    {
        // MAPPING TẠM THỜI (Giả lập DB Provinces)
        // Bạn cần map các ID mà Frontend gửi lên tương ứng với tên
        $provinces = [
            1  => 'Hà Nội',
            79 => 'Hồ Chí Minh',
            48 => 'Đà Nẵng',
            31 => 'Hải Phòng',
            92 => 'Cần Thơ',
            36 => 'Thanh Hóa',
            // ... thêm các ID khác nếu cần test
        ];

        return $provinces[$provinceId] ?? 'Other';
    }
}