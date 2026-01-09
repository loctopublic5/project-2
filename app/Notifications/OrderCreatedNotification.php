<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Quan trọng để không treo server
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    /**
     * Constructor: Nhận Order để xử lý
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * KÊNH GỬI (THE CORE SWITCH)
     * Quyết định thông báo này sẽ đi qua những đường nào.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // Gửi cả Email và Lưu vào DB
    }

    /**
     * KÊNH EMAIL: Xây dựng nội dung Email
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Xác nhận đơn hàng #' . $this->order->id) // Tiêu đề Email
            ->view('emails.orders.confirmation', [ // Sử dụng Blade View
                'order' => $this->order,
                'notifiable' => $notifiable
            ]);
    }

    /**
     * KÊNH DATABASE: Định nghĩa dữ liệu lưu vào cột `data` trong bảng `notifications`
     * Dữ liệu này sẽ được convert sang JSON.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'title'    => 'Đặt hàng thành công!',
            'message'  => "Đơn hàng #{$this->order->id} của bạn đã được ghi nhận và đang chờ xử lý.",
            'type'     => 'order_success', // Giúp Frontend hiển thị icon (ví dụ: icon giỏ hàng màu xanh)
            'amount'   => $this->order->total_amount, // Có thể lưu thêm tổng tiền để hiển thị nhanh
            'time'     => now()->toDateTimeString(),
        ];
    }
}