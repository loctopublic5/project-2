<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;

class PricingService{

    /**
     * Hàm tính toán tổng hợp
     * @param array $cartItems: Danh sách SP và số lượng
     * @param string|null $voucherCode: Mã giảm giá user nhập
     * @return array: Cấu trúc giá chi tiết
     */
    public function  calculateCart($cartItems, $voucherCode = NULL){

        // --- BƯỚC 1: TÍNH SUBTOTAL (TỔNG TIỀN HÀNG) ---
        $subtotal = 0;
        $lineItems = []

        foreach item IN cartItems:
            // Lấy thông tin sản phẩm
            product = FindProduct(item.product_id)

            // LOGIC B2C: Ưu tiên giá Sale 
            // Điều kiện: Có giá sale VÀ giá sale nhỏ hơn giá gốc
            IF (product.sale_price > 0) AND (product.sale_price < product.price):
                finalUnitPrice = product.sale_price
            ELSE:
                finalUnitPrice = product.price
            END IF

            lineTotal = finalUnitPrice * item.quantity
            subtotal = subtotal + lineTotal

            // Lưu lại snapshot để sau này lưu vào bảng order_items [cite: 84]
            PUSH {
                'product_id': product.id,
                'price_at_purchase': finalUnitPrice,
                'quantity': item.quantity
            } INTO lineItems
        END FOR

        // --- BƯỚC 2: TÍNH GIẢM GIÁ (VOUCHER) ---
        INIT discountAmount = 0
        INIT voucherInfo = NULL

        IF voucherCode IS NOT NULL:
            // Gọi hàm validation riêng (Clean Code)
            voucher = ValidateVoucher(voucherCode, subtotal) // Validate ngày, số lượng, min_order_value

            IF voucher IS VALID:
                IF voucher.type == 'percent':
                    discountAmount = subtotal * (voucher.value / 100)
                    // Nếu có logic max_discount thì check ở đây
                ELSE:
                    discountAmount = voucher.value // Loại Fixed
                END IF
                
                // Cập nhật thông tin trả về
                voucherInfo = voucher
            END IF
        END IF

        // --- BƯỚC 3: CHỐT TỔNG (FINAL TOTAL) ---
        // Phí ship tạm tính là 0 (sẽ tính ở ShippingService sau)
        shippingFee = 0 

        // Công thức chuẩn[cite: 121]: (Hàng - Giảm giá) + Ship
        // Sử dụng hàm MAX để tránh âm tiền (Bài học Câu 1)
        total = MAX(0, subtotal - discountAmount) + shippingFee

        RETURN {
            'subtotal': subtotal,
            'discount_amount': discountAmount,
            'shipping_fee': shippingFee,
            'total': total,
            'line_items': lineItems, // Dùng để insert vào order_items
            'voucher_used': voucherInfo
        }

    /**
     * Hàm kiểm tra Voucher (Re-usable)
     */
    FUNCTION ValidateVoucher(code, orderValue):
        voucher = FindVoucher(code)

        // Check 1: Tồn tại và Còn số lượng [cite: 73]
        IF !voucher OR voucher.quantity <= 0: THROW Error

        // Check 2: Thời gian (Khắc phục lỗi Câu 2) [cite: 74, 77]
        // Bắt buộc so sánh với NOW() ngay lúc gọi hàm
        IF NOW() < voucher.start_date OR NOW() > voucher.end_date: THROW Error

        // Check 3: Giá trị đơn tối thiểu [cite: 73]
        IF orderValue < voucher.min_order_value: THROW Error

        RETURN voucher
    }
}