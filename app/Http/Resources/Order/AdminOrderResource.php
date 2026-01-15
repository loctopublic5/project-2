<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use App\Http\Resources\Order\OrderItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. XỬ LÝ TÁCH NOTE VÀ REASON
        // Mặc định note là toàn bộ chuỗi
        $customerNote = $this->note;
        $cancelReason = null;

        // Nếu tìm thấy vách ngăn " ||| "
        if ($this->note && str_contains($this->note, ' ||| ')) {
            $parts = explode(' ||| ', $this->note);
            $customerNote = $parts[0]; // Phần đầu là note của khách
            $cancelReason = $parts[1] ?? null; // Phần sau là lý do hủy
        }
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            
            // --- KHÁC BIỆT SO VỚI CLIENT: Phải hiện User info ---
            'customer'       => [
                'id'        => $this->user_id,
                'full_name' => $this->user->full_name ?? 'Khách vãng lai',
                'email'     => $this->user->email ?? null,
                'phone'     => $this->user->phone ?? null,
            ],

            // Trạng thái (Format đẹp cho Frontend Admin Dashboard)
            'status'         => $this->formatStatus($this->status),
            
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            
            // Tài chính
            'total_amount'   => $this->total_amount,
            'shipping_fee'   => $this->shipping_fee,
            
            // Địa chỉ & Note
            'shipping_address' => $this->shipping_address, // Giả sử cột này lưu JSON
            'note'          => $customerNote,  // Chỉ hiện note của khách ("Giao giờ hành chính...")
            'cancel_reason' => $cancelReason,

            // Thời gian
            'created_at'     => $this->created_at->format('d/m/Y H:i'),
            'updated_at'     => $this->updated_at->format('d/m/Y H:i'),

            // Items (Sản phẩm trong đơn)
            'items'          => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }

    /**
     * Helper format màu sắc cho Status Admin Dashboard
     */
    private function formatStatus($status)
    {
        // Lấy value nếu là Enum
        $value = $status->value ?? $status;

        $labels = [
            'pending'   => ['label' => 'Chờ xử lý',  'color' => 'warning'],
            'confirmed' => ['label' => 'Đã duyệt',   'color' => 'info'],
            'shipping'  => ['label' => 'Đang giao',  'color' => 'primary'],
            'completed' => ['label' => 'Hoàn thành', 'color' => 'success'],
            'cancelled' => ['label' => 'Đã hủy',     'color' => 'danger'],
            'returned'  => ['label' => 'Trả hàng',   'color' => 'secondary'],
        ];

        return [
            'key'   => $value,
            'label' => $labels[$value]['label'] ?? $value,
            'color' => $labels[$value]['color'] ?? 'secondary', // Dùng cho Badge UI (Bootstrap/AntDesign)
        ];
    }
}