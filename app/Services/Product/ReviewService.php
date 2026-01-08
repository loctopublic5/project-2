<?php
namespace App\Services\Product;

use Exception;
use App\Models\Order;
use App\Models\Review;
use App\Enums\OrderStatus;

class ReviewService
{
    /**
     * Tạo đánh giá sản phẩm (Verified Purchase Logic)
     */
    public function createReview($user, $productId, array $data)
    {
        $orderId = $data['order_id'];

        // 1. TÌM ĐƠN HÀNG & CHECK SỞ HỮU (Gộp làm 1)
        // Eager load 'items' để dùng cho bước 3
        $order = Order::with('items')
            ->where('id', $orderId)
            ->where('user_id', $user->id) // <--- Check quyền sở hữu ở đây rồi
            ->first();

        if (!$order) {
            throw new Exception("Đơn hàng không tồn tại hoặc không thuộc về bạn.", 404);
        }

        // 2. CHECK TRẠNG THÁI (Business Rule)
        // Support cả Enum Value hoặc String thường (đề phòng)
        $currentStatus = $order->status instanceof OrderStatus ? $order->status->value : $order->status;
        $completedStatus = OrderStatus::COMPLETED instanceof OrderStatus ? OrderStatus::COMPLETED->value : 'completed';

        if ($currentStatus !== $completedStatus) {
            throw new Exception("Chỉ những đơn hàng đã hoàn thành mới được phép đánh giá.", 400);
        }

        // 3. CHECK SẢN PHẨM CÓ TRONG ĐƠN KHÔNG? (Integrity)
        // (Bước 2 cũ của bạn bị thừa, chỉ cần check bước này là đủ)
        if (!$order->items->contains('product_id', $productId)) {
            throw new Exception("Sản phẩm ID {$productId} không nằm trong đơn hàng này.", 400); // Message rõ hơn chút
        }

        // 4. CHECK ĐÃ REVIEW CHƯA? (Duplicate Check)
        $exists = Review::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->where('order_id', $orderId)
            ->exists();

        if ($exists) {
            throw new Exception("Bạn đã đánh giá sản phẩm này trong đơn hàng này rồi.", 400);
        }

        // 5. TẠO REVIEW
        $review = Review::create([
            'user_id'    => $user->id,
            'product_id' => $productId,
            'order_id'   => $orderId,
            'rating'     => $data['rating'],
            'comment'    => $data['comment'] ?? null,
            'is_active'  => true,
        ]);

        return $review;
    }
}  