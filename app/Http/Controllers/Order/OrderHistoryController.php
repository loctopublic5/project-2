<?php

namespace App\Http\Controllers\Order;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Http\Resources\OrderResource;

class OrderHistoryController extends Controller
{
    use ApiResponse;
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Danh sách đơn hàng của tôi
     */
    public function index(Request $request)
    {
        try{
            // 1. Lấy User ID hiện tại
            $userId = $request->user()->id;

            // 2. Gọi Service (Tái sử dụng logic lọc/sort)
            // Truyền params từ request (status, keyword...) vào
            $orders = $this->orderService->getOrders($userId, $request->all());

            // 3. Trả về Resource Collection
            $result =  OrderResource::collection($orders);

            return $this->succes($result, 'Láy danh sách đơn hàng thành công');
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
        
    }

    /**
     * Chi tiết đơn hàng
     */
    public function show(Request $request, $id)
    {
        try{
            $userId = $request->user()->id;

            // Gọi Service lấy chi tiết (Service tự check security user_id)
            $order = $this->orderService->getOrderDetail($id, $userId);

            // Trả về Resource Single
            return $this->success(new OrderResource($order),'Lấy thành công chi tiết đơn hàng');
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
    }
}