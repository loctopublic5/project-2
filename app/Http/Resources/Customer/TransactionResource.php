<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'code'         => $this->code,          // Mã giao dịch (VD: DEP-XK9L2M)
            'reference_id' => $this->reference_id,  // Mã đơn hàng liên quan (nếu có)

            // 2. Số tiền (Quan trọng)
            // Trả về số thô để tính toán
            'amount'       => (float) $this->amount,
            
            // Trả về chuỗi đã format để hiển thị (VD: "+ 50,000 đ" hoặc "- 20,000 đ")
            'amount_fmt'   => $this->formatMoney($this->amount),

            // 3. Phân loại (Type)
            'type'         => $this->type,
            'type_label'   => $this->getTypeLabel($this->type), // Nạp tiền / Thanh toán...

            // 4. Trạng thái (Status)
            'status'       => $this->status,
            'status_label' => $this->getStatusLabel($this->status), // Đang xử lý / Thành công...
            'status_color' => $this->getStatusColor($this->status), // Gợi ý màu cho FE (green, yellow, red)

            // 5. Thông tin bổ sung
            'description'  => $this->description,
            'created_at'   => $this->created_at->format('d/m/Y H:i'), // Format ngày giờ VN
        ];
    }

// --- PRIVATE HELPERS (Giữ logic hiển thị gọn gàng) ---

    private function formatMoney($amount)
    {
        // Nếu số dương -> Thêm dấu cộng
        // Nếu số âm -> number_format tự thêm dấu trừ
        $prefix = $amount > 0 ? '+' : '';
        return $prefix . number_format($amount, 0, ',', '.') . ' đ';
    }

    private function getTypeLabel($type)
    {
        return match ($type) {
            'deposit' => 'Nạp tiền',
            'payment' => 'Thanh toán đơn hàng',
            'refund'  => 'Hoàn tiền',
            default   => ucfirst($type),
        };
    }

    private function getStatusLabel($status)
    {
        return match ($status) {
            'pending' => 'Đang xử lý',
            'success' => 'Thành công',
            'failed'  => 'Thất bại',
            default   => ucfirst($status),
        };
    }

    private function getStatusColor($status)
    {
        // Gợi ý màu sắc để Frontend render badge/tag
        return match ($status) {
            'success' => 'green',  // Hoặc mã hex #10B981
            'pending' => 'yellow', // Hoặc mã hex #F59E0B
            'failed'  => 'red',    // Hoặc mã hex #EF4444
            default   => 'gray',
        };
    }
}
