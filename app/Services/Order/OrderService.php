<?php 
namespace App\Services\Order;

use Exception;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use App\Services\Customer\WalletService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class OrderService 
{

    public function __construct(protected WalletService $walletService){}
    // =========================================================================
    // 1. DÀNH CHO ADMIN (QUẢN LÝ TOÀN BỘ)
    // =========================================================================

    /**
     * Admin lấy danh sách đơn hàng (Full quyền, Full Search)
     */
    public function getOrdersForAdmin(array $filters = [], $perPage = 20)
    {
        // Khởi tạo query không có điều kiện user_id -> Lấy hết
        $query = Order::query();

        // 1. EAGER LOADING
        // Admin cần biết chi tiết User (Tên, SĐT) và Items để preview
        $query->with(['items.product', 'user:id,full_name,email,phone']);

        // 2. FILTERING CHUNG
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // 3. ADVANCED SEARCH (Dành riêng cho Admin)
        // Admin cần tìm theo: Mã đơn OR Tên khách OR SĐT khách
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function (Builder $q) use ($keyword) {
                // Tìm theo Code
                $q->where('code', 'like', "%{$keyword}%")
                  // Hoặc tìm trong bảng User
                  ->orWhereHas('user', function ($subQ) use ($keyword) {
                      $subQ->where('full_name', 'like', "%{$keyword}%")
                           ->orWhere('phone', 'like', "%{$keyword}%")
                           ->orWhere('email', 'like', "%{$keyword}%");
                  });
            });
        }

        // 4. SORTING
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Admin xem chi tiết đơn hàng (Không check owner)
     */
    public function getOrderDetailForAdmin($id)
    {
        $order = Order::with([ 'user:id,full_name,email,phone', 'items.product'])
            ->where(function($q) use ($id) {
                $q->where('id', $id)->orWhere('code', $id);
            })
            ->first();

        if (!$order) {
            throw new ModelNotFoundException("Đơn hàng không tồn tại.");
        }

        return $order;
    }

    /**
 * Xác nhận đã nhận hàng (Customer confirm)
 */
public function confirmReceived($userId, $orderId)
{
    return DB::transaction(function () use ($userId, $orderId) {
        // 1. Tìm đơn hàng (Security check: Đúng chủ nhân)
        $order = Order::where('user_id', $userId)
                      ->where('id', $orderId)
                      ->firstOrFail();

        // 2. Kiểm tra trạng thái: Chỉ được xác nhận khi đang giao hàng (shipping)
        // Lưu ý: Tùy vào Enum của bạn, thay đổi 'shipping' cho đúng key
        if ($order->status->value !== 'shipping') {
            throw new Exception("Đơn hàng không ở trạng thái có thể xác nhận.");
        }

        // 3. Cập nhật trạng thái
        $order->update([
            'status' => 'completed', // Chuyển sang Hoàn thành
            'completed_at' => now(),
            'payment_status' => 'paid' // Đảm bảo thanh toán đã hoàn tất
        ]);

        return $order;
    });
}
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
        $query->with(['items', 'user:id,full_name,email']);

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
                        ->where('user_id', $user->id)
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

    /**
     *  Cập nhật trạng thái đơn hàng (Admin/Kho)
     * * @param int|string $orderId
     * @param string $newStatus (confirmed, shipping, cancelled, completed)
     * @param User $actor (Người thực hiện: Admin hoặc Warehouse)
     * @return Order
     * @throws ModelNotFoundException | Exception
     */
    public function updateStatusByAdmin($orderId, $newStatus,User $actor,$reason = null){
        // 1. DB: Tìm đơn hàng (Nếu không có -> Ném ModelNotFoundException -> Controller xử lý 404)
        $order = Order::where('id', $orderId)
                    ->orWhere('code', $orderId)
                    ->first();
        if(!$order){
            throw new ModelNotFoundException("Đơn hàng không tồn tại.");
        }
        // Lấy value string của status hiện tại
        $currentStatus = $order->status->value ?? $order->status;

        // 2. Logic: Idempotency (Trùng status thì trả về luôn, không tốn resource DB)
        if($currentStatus === $newStatus){
            return $order;
        }

        // 3. Logic: State Machine & Permission Check
        // 3. CORE LOGIC: STATE MACHINE & PERMISSION
            switch ($newStatus) {

                // --- CASE A: DUYỆT ĐƠN (Pending -> Confirmed) ---
                case 'confirmed':
                    // Rule 1: State Check
                    if ($currentStatus !== 'pending') {
                        throw ValidationException::withMessages(['status' => "Chỉ đơn 'Chờ xử lý' mới được Xác nhận."]);
                    }
                    // Rule 2: Permission Check (Admin/Sale)
                    if (!$actor->hasPermissionTo('orders', 'approve')) {
                        throw new Exception("Bạn không có quyền Xác nhận đơn hàng.", 403);
                    }

                    // *** SIDE EFFECT: TRỪ TỒN KHO (Hard Reserve) ***
                    // Logic: Khi duyệt đơn, kho thực tế sẽ bị trừ
                    $this->deductStock($order); 
                    break;

                // --- CASE B: GIAO HÀNG (Confirmed -> Shipping) ---
                case 'shipping':
                    // Rule 1: State Check
                    if ($currentStatus !== 'confirmed') {
                        throw ValidationException::withMessages(['status' => "Đơn phải được 'Xác nhận' xong mới được Giao đi."]);
                    }
                    // Rule 2: Permission Check (Warehouse)
                    if (!$actor->hasPermissionTo('orders', 'ship')) {
                        throw new Exception("Bạn không có quyền Xuất kho.", 403);
                    }
                    // Ở bước này kho đã trừ lúc confirm rồi, không trừ nữa.
                    break;

                // --- CASE C: HỦY ĐƠN (Any -> Cancelled) ---
                case 'cancelled':
                    // Rule 1: State Check
                    if (in_array($currentStatus, ['completed', 'returned'])) {
                        throw ValidationException::withMessages(['status' => "Không thể hủy đơn hàng đã hoàn tất/trả hàng."]);
                    }
                    // Rule 2: Permission Check (Admin)
                    if (!$actor->hasPermissionTo('orders', 'cancel')) {
                        throw new Exception("Bạn không có quyền Hủy đơn hàng.", 403);
                    }

                    // *** SIDE EFFECT 1: HOÀN KHO (Restock) ***
                    // Chỉ hoàn kho nếu đơn đã từng trừ kho (Confirmed/Shipping)
                    if (in_array($currentStatus, ['confirmed', 'shipping'])) {
                        $this->restockItems($order);
                    }
                    
                    // *** SIDE EFFECT 2: HOÀN TIỀN (Refund) ***
                    $this->processRefundIfNeeded($order, $reason ?? "Admin hủy đơn");
                    if ($newStatus === 'cancelled' && $reason) {
                        // Dùng " ||| " làm vách ngăn giữa Note khách và Lý do Admin
                        // Logic: Note cũ + Vách ngăn + Lý do mới
                            $order->note = $order->note . " ||| " . $reason;
                        // Hoàn tiền nếu cần (Code cũ của bạn)
                        $this->processRefundIfNeeded($order, $reason);
                    }
                    break;
                
                // --- CASE D: HOÀN THÀNH (Force Complete) ---
                case 'completed':
                    if ($currentStatus !== 'shipping') {
                        throw ValidationException::withMessages(['status' => "Chỉ đơn đang giao mới chuyển thành Hoàn thành được."]);
                    }
                    if (!$actor->hasPermissionTo('orders', 'complete')) {
                        throw new Exception("Bạn không có quyền Hoàn tất đơn hàng.", 403);
                    }
                    
                     // Nếu là COD và chưa thanh toán -> Update thành Paid
                    if ($order->payment_method === 'COD' && $order->payment_status !== 'paid') {
                         $order->payment_status = 'paid'; // Hoặc Enum PaymentStatus::PAID
                    }
                    break;

                default:
                    throw ValidationException::withMessages(['status' => "Trạng thái '{$newStatus}' không hợp lệ."]);
            }
        // 4. DB: Lưu trạng thái mới
        
        $order->status = $newStatus;
        $order->save();

        // 5. Output: Trả về Model cho Controller
        return $order;
    }
    // =========================================================================
    // PRIVATE HELPERS (Logic cốt lõi dùng chung)
    // =========================================================================

    /**
     * Logic Trừ kho (Khi Confirm đơn)
     */
    private function deductStock(Order $order)
    {
        // Load items để tránh query N+1
        $order->load('items');

        foreach ($order->items as $item) {
            // Lock sản phẩm để tránh Race Condition (2 đơn cùng mua cái cuối cùng)
            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

            if (!$product) continue;

            if ($product->stock_qty < $item->quantity) {
                throw new Exception("Sản phẩm '{$product->name}' không đủ tồn kho (Còn: {$product->stock_qty}).", 400);
            }

            $product->decrement('stock_qty', $item->quantity);
        }
    }

    /**
     * Logic Hoàn kho (Khi Hủy đơn đã duyệt)
     */
    private function restockItems(Order $order)
    {
        $order->load('items');
        foreach ($order->items as $item) {
            // Dùng increment trực tiếp cho nhanh, không cần lock vì cộng thì thoải mái
            Product::where('id', $item->product_id)->increment('stock_qty', $item->quantity);
        }
    }

    /**
     * Logic Hoàn tiền Ví (Kết nối WalletService của bạn)
     */
    private function processRefundIfNeeded(Order $order, $reason)
    {
        // Kiểm tra xem đã thanh toán chưa (paid) và qua kênh Wallet
        $isPaid = ($order->payment_status === 'paid' || $order->payment_status->value === 'paid'); 
        
        if ($isPaid && $order->payment_method === 'wallet') {
            
            // GỌI HÀM CỦA BẠN ĐÂY
            $this->walletService->refundForOrder($order->user, [
                'amount'            => $order->total_amount,
                'original_order_id' => $order->code, // Hoặc $order->id tùy DB của bạn
                'reason'            => $reason
            ]);
            
            // Cập nhật trạng thái thanh toán của đơn
            $order->payment_status = PaymentStatus::REFUNDED;
        }
    }
}

