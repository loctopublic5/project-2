<?php

namespace App\Http\Controllers\Product;

use Exception;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Product\ReviewService;
use App\Http\Resources\Product\ReviewResource;
use App\Http\Requests\Product\StoreReviewRequest;

class ProductReviewController extends Controller
{
    use ApiResponse;

    public function __construct(protected ReviewService $reviewService) {}

    /**
     * POST /api/products/{id}/reviews
     */
    public function store(StoreReviewRequest $request, $productId){
        try{
            $user = $request->user();
            $review = $this->reviewService->createReview(
                $user,
                $productId,
                $request->validated()
            );
            return $this->success(new ReviewResource($review->load('user')), 'Đánh giá sản phẩm thành công.', 201);
        }catch (Exception $e){
            // Lấy code từ Exception
            $statusCode = $e->getCode();

            // ⚠️ QUAN TRỌNG:
            // Exception của Database (QueryException) thường trả về string "23000", "42S22"...
            // Exception thông thường trả về 0 nếu không set code.
            // Ta phải kiểm tra xem nó có phải là HTTP Code hợp lệ (100-599) hay không.
            if (!is_int($statusCode) || $statusCode < 100 || $statusCode > 599) {
                $statusCode = 400; // Mặc định về 400 Bad Request nếu mã lỗi lạ
            }
            return $this->error($e->getMessage(),$statusCode);
        }
    }

    /**
     * GET /api/products/{id}/reviews
     * Lấy danh sách đánh giá (Có phân trang)
     */
    public function index($productId)
    {
        // 1. Gọi Service lấy dữ liệu phân trang
        $reviews = $this->reviewService->getProductReviews($productId, 10); // 10 review/trang

        // 2. Trả về Collection Resource
        // Laravel sẽ tự động đóng gói thêm meta data (current_page, last_page, total...)
        return ReviewResource::collection($reviews);
    }
}
