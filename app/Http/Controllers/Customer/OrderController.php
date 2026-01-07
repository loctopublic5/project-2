<?php

namespace App\Http\Controllers\Customer;

use App\Traits\ApiResponse;
use Exception;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Http\Resources\Customer\OrderResource;
use App\Http\Requests\Customer\StoreOrderRequest;


class OrderController extends Controller
{
    use ApiResponse;
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request)
    {
        try {
            // 1. Lấy User hiện tại
            $user = $request->user();
            $request->validated(); 

            // 2. Prepare Data (Gọi Service bước chuẩn bị)
            // Controller chỉ làm nhiệm vụ "người vận chuyển" dữ liệu
            $orderData = $this->orderService->prepareOrderData(
                $user,
                $request->address_id,
                $request->payment_method,
                $request->voucher_code, // Tự động null nếu không gửi
                $request->note          // Tự động null nếu không gửi
            );

            // 3. Create Order (Gọi Service bước Transaction)
            $order = $this->orderService->createOrder($user, $orderData);

            // 4. Eager load items để trả về Resource đẹp luôn (Tránh N+1 khi format JSON)
            $order->load('items');

            // 5. Trả về thành công
            return $this->success(new OrderResource($order), 'Đặt hàng thành công!', 201);

        } catch (Exception $e) {
            // Service ném lỗi gì (Hết hàng, Hết tiền ví...) thì bắt ở đây
            // Trả về 400 Bad Request
            return $this->error($e->getMessage(), 400);
        }
    }
}