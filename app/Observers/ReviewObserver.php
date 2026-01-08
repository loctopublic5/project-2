<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\Product;

class ReviewObserver
{
    /**
     * LOGIC CỐT LÕI: Tính trung bình và update vào bảng Product
     */
    public function recalculateProductRating($productId){
        // 1. Tính toán Aggregate trực tiếp từ DB (Rất nhanh)
        // Chỉ tính các review đang active (nếu bạn có tính năng ẩn review)
        $result = Review::where('product_id', $productId)
                        ->where('is_active', true) 
                        ->selectRaw('avg(rating) as avg_rating, count(*) as total_reviews')
                        ->first();

        // 2. Xử lý số liệu (Nếu null tức là không còn review nào -> về 0)
        $avgRating = $result->avg_rating ? round($result->avg_rating, 1) : 0; // Làm tròn 1 chữ số thập phân (4.5)
        $totalReviews = $result->total_reviews ?? 0;

        // 3. Update vào bảng Products (Silent Update - không cần trigger event của Product)
        Product::where('id', $productId)->update([
            'rating_avg' => $avgRating,
            'review_count' => $totalReviews
        ]);
    }
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $this->recalculateProductRating($review->product_id);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        // Nếu user sửa số sao, phải tính lại
        // Dùng isDirty để check xem cột rating có thay đổi không cho tối ưu
        if ($review->isDirty('rating') || $review->isDirty('is_active')) {
            $this->recalculateProductRating($review->product_id);
        }
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        $this->recalculateProductRating($review->product_id);
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }
}
