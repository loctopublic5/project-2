<?php
namespace App\Services\Order;

use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Exceptions\VoucherException;

class VoucherService{
    /**
     * @param string $code
     * @param float $cartTotal
     * @param int|null $userId (Mới thêm)
     * @return Voucher
     * @throws VoucherException
     */
    public function validateVoucher(string $code, float $cartTotal, ?int $userId = null): Voucher
    {
        // 1. Tìm & Check cơ bản (Code, Active, Date, Qty)
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) throw new VoucherException("Mã giảm giá không tồn tại.");
        if (!$voucher->is_active) throw new VoucherException("Mã giảm giá đang tạm khóa.");
        if ($voucher->quantity <= 0) throw new VoucherException("Mã giảm giá đã hết lượt sử dụng toàn hệ thống.");
        
        $now = now();
        if ($now->lt($voucher->start_date)) throw new VoucherException("Mã giảm giá chưa mở.");
        if ($now->gt($voucher->end_date)) throw new VoucherException("Mã giảm giá đã hết hạn.");

        // 2. Check giá trị đơn hàng
        if ($cartTotal < $voucher->min_order_value) {
            $missing = number_format($voucher->min_order_value);
            throw new VoucherException("Đơn hàng phải từ {$missing}đ để áp dụng mã này.");
        }

        // 3. Check User Usage (LOGIC MỚI)
        if ($userId) {
            // Đếm số lần user này đã dùng voucher này
            $usageCount = VoucherUsage::where('user_id', $userId)
                                        ->where('voucher_id', $voucher->id)
                                        ->count();

            if ($usageCount >= $voucher->limit_per_user) {
                throw new VoucherException("Bạn đã dùng hết số lượt cho phép của mã này.");
            }
        } 
        // Optional: Nếu voucher bắt buộc login mới được dùng
        else {
            throw new VoucherException("Vui lòng đăng nhập để sử dụng mã này.");
        }

        return $voucher;
    }
}