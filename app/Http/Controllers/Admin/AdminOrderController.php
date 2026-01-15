<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Http\Resources\Order\OrderResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Order\AdminOrderResource;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class AdminOrderController extends Controller
{
    use ApiResponse;
    public function __construct(protected OrderService $orderService){}

    /**
     * API: Cập nhật trạng thái đơn hàng (Dành cho Admin/Warehouse)
     * Method: PATCH
     * URL: /api/admin/orders/{id}/status
     */
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        try {
            // 1. Lấy dữ liệu đã validate (status, reason)
            $validated = $request->validated();
            $status = $validated['status'];
            $actor = $request->user(); // Người đang thao tác

            // 2. Gọi Service xử lý (Bao gồm cả check quyền, kho, tiền)
            $order = $this->orderService->updateStatusByAdmin($id, $status, $actor);

            // 3. Trả về kết quả (Dùng Resource để format đẹp)
            return $this->success(new AdminOrderResource($order), 'Cập nhật trạng thái đơn hàng thành công.');

        } catch (ModelNotFoundException $e) {
            // Lỗi không tìm thấy đơn
            return $this->error('Đơn hàng không tồn tại.', 404);

        } catch (ValidationException $e) {
            // Lỗi Validation do Service ném ra (Ví dụ: Sai quy trình State Machine)
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(), // VD: "The given data was invalid." hoặc message tùy chỉnh
                'errors'  => $e->errors()      // VD: ['status' => ['Đơn phải được xác nhận...']]
            ], 422);

        } catch (Exception $e) {
            // Các lỗi logic khác (Lỗi kho, Lỗi quyền 403...)
            // Service ném code bao nhiêu thì trả về bấy nhiêu (400 hoặc 403)
            $statusCode = $e->getCode() ?: 400; 
            return $this->error($e->getMessage(), $statusCode);
        }
    }
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
            $orders = $this->orderService->getOrdersForAdmin( $filters,20);

            // 3. Trả về Resource Collection
            $result =  AdminOrderResource::collection($orders);

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
    public function show( $id)
    {
        try {
            // Gọi Service: getOrderDetail(id, userId=null -> Admin)
            $order = $this->orderService->getOrderDetailForAdmin($id);
            
            return $this->success(new AdminOrderResource($order), 'Lấy chi tiết thành công');

        } catch (ModelNotFoundException $e) {
            return $this->error('Không tìm thấy đơn hàng', 404);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}