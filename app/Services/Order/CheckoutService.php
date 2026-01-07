<?php 
namespace App\Services\Order;

use Exception;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use App\Services\Customer\WalletService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CheckoutService 
{

    public function __construct(protected WalletService $walletService){}
    /**
     * Lấy danh sách đơn hàng (Dùng chung cho cả Admin và Customer)
     * * @param int|null $userId : Nếu null -> Admin (lấy hết). Nếu có ID -> Customer (lấy của mình).
     * @param array $filters : Các bộ lọc (status, date, keyword...)
     * @param int $perPage
     */
    public function getOrders($userId = null, array $filters = [], $perPage = 10){
        $query = Order::query();

        // 1. SCOPE BY USER (SECURITY CORE)
        if($userId){
            $query->where('user_id', $userId);
        }

        // 2. EAGER LOADING (PERFORMANCE)
        // Load sơ bộ items để hiển thị hình ảnh preview bên ngoài (nếu cần)
        // Load user để Admin biết đơn của ai
        $query->with(['items', 'user:id,name,email']);

        // 3. FILTERING 

        // Lọc theo Status
        if(!empty($filters['status'])){
            $query->where('status', $filters['status']);
        }

        // Lọc theo Payment Status
        if(!empty($filters['payment_status'])){
            $query->where('payment_status', $filters['payment_status']);
        }

        // Tìm kiếm theo mã đơn
        if(!empty($filters['keyword'])){
            $keyword = $filters['keyword'];
            $query->where('code', 'like', "%{$keyword}%");
        }

        // Lọc theo ngày tháng
        if(!empty($filters['date_from'])){
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if(!empty($filters['date_to'])){
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // 4. SORTING
    $query->orderBy('created_at', 'desc');
    return $query->paginate($perPage);
    }

    /**
     * Lấy chi tiết đơn hàng
     * * @param string|int $orderId : ID hoặc Code đơn hàng
     * @param int|null $userId : Context người xem (để check quyền)
     */
    public function getOrderDetail($orderId, $userId = null){
        $query = Order::query()->with(['items.product', 'user']);

        // Tìm theo ID
        $query->where(function($q) use ($orderId){
            $q->where('id', $orderId)
            ->orWhere('code', $orderId);
        });

        // SECURITY CHECK
        // Nếu là Customer, bắt buộc đơn này phải thuộc về họ
        if($userId){
            $query->where('user_id', $userId);
        }
        $order = $query->first();
        if(!$order){
            throw new ModelNotFoundException("Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.");
        }
        return $order;
    }

    /**
     * Khách hàng hủy đơn
     */
    public function cancelOrder($user, $orderId, $reason){
        return DB::transaction(function () use ($user,$orderId, $reason){
            // 1. Lấy đơn hàng (Có check quyền sở hữu của User)
            // Dùng lockForUpdate để tránh Admin confirm cùng lúc User bấm hủy
            $order = Order::where('id', $orderId)
                        ->where('user_id', $orderId)
                        ->lockForUpdate()
                        ->first();
        
            if(!$order){
                throw new Exception("Đơn hàng không tồn tại.");
            }

            /// 2. CHECK TRẠNG THÁI (Guard Clause)
            // Chỉ cho hủy khi còn Pending
            if ($order->status !== OrderStatus::PENDING) {
                throw new ModelNotFoundException("Không thể hủy đơn hàng đã được xác nhận hoặc đang vận chuyển.");
            }

            // 3. CẬP NHẬT TRẠNG THÁI
            $order->status = OrderStatus::CANCELLED;
            $order->note   = $order->note . " | Lý do hủy: " . $reason;
            
            // 4. HOÀN TIỀN (REFUND LOGIC)
            if($order->payment_status === PaymentStatus::PAID && $order->payment_method === 'wallet'){
                $this->walletService->refundForOrder($user, [
                    'amount'            => $order->total_amount,
                    'original_order_id' => $order->code,
                    'reason'            => $reason
                ]);
                $order->payment_status = PaymentStatus::REFUNDED;
            }
            $order->save();

            // 5. HOÀN KHO (RESTOCK)
            // Lặp qua items để cộng lại số lượng tồn kho
            // Tương tự logic trừ kho, ta dùng increment cho an toàn
            foreach ($order->items as $item) {
                DB::table('products')
                    ->where('id', $item->product_id)
                    ->increment('stock_qty', $item->quantity);
            }

            return $order;
        });
    }
}