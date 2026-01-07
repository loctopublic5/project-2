<?php

namespace App\Http\Controllers\Order;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Http\Resources\Customer\OrderResource;



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
            $filters = $request->all();

            // 2. Gọi Service (Tái sử dụng logic lọc/sort)
            // Truyền params từ request (status, keyword...) vào
            $orders = $this->orderService->getOrders($userId, $filters);

            // 3. Trả về Resource Collection
            $result =  OrderResource::collection($orders);

            $message = $orders->isEmpty() 
            ? 'Không tìm thấy đơn hàng phù hợp.' 
            : 'Lấy danh sách đơn hàng thành công.';

            return $this->success($result, $message);
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
        
    }

    /**
     * Chi tiết đơn hàng
     */
    public function show(Request $request, $id)
    {
        
            $userId = $request->user()->id;

            // Gọi Service lấy chi tiết (Service tự check security user_id)
            $order = $this->orderService->getOrderDetail($id, $userId);

            // Trả về Resource Single
            return $this->success(new OrderResource($order),'Lấy thành công chi tiết đơn hàng');

    }

    /**
     * Hủy đơn hàn pending và hoàn tiền, hoàn kho
     */
    public function cancel(Request $request, $id){
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            $order = $this->orderService->cancelOrder(
                $request->user(), 
                $id, 
                $request->reason
            );

            return $this->success(new OrderResource($order), 'Hủy đơn thành công');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}