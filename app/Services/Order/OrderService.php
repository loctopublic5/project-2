<?php 
namespace App\Services\Order;

use Exception;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\Customer\CartService;
use App\Services\System\PricingService;
use App\Services\Customer\WalletService;

class OrderService{

    public function __construct(
        protected PricingService $pricingService,
        protected CartService $cartService,
        protected WalletService $walletService
    ){}

    /**
     * Hàm này chuẩn bị dữ liệu, validate và tính toán tiền nong.
     * Trả về một mảng data "Sạch" để bước sau chỉ việc Insert vào DB.
     */
    public function prepareOrderData($user, $addressId, $paymentMethod, $voucherCode = null, $note = null){
        // 1. VALIDATE ADDRESS
        // Phải đảm bảo address này tồn tại VÀ thuộc về user này (Chống IDOR)
        $address = UserAddress::where('user_id', $user->id)->find($addressId);
        if(!$address){
            throw new Exception('Địa chỉ giao hàng không hợp lệ hoặc không thuộc về bạn.');
        }

        // 2. GỌI CARTSERVICE
        // Lấy danh sách sản phẩm từ giỏ hàng hiện tại
        $cartResult = $this->cartService->getCartDetail($user->id, [
            'voucher_code' => $voucherCode,
            'address_id'   => $addressId
        ]);

        // Trích xuất dữ liệu từ kết quả trả về của CartService
        /** @var \App\Models\Cart $cartModel */
        $cartModel     = $cartResult['cart'];    // Object chứa quan hệ items
        $pricingResult = $cartResult['pricing']; // Mảng kết quả tính tiền (subtotal, total...)

        // Lấy danh sách item ĐƯỢC CHỌN MUA (selected = true)
        // Lưu ý: Theo code của bạn là $cartModel->items
        $selectedItems = ($cartModel->items ?? collect([]))->where('selected', true);

        if ($selectedItems->isEmpty()) {
            throw new Exception('Bạn chưa chọn sản phẩm nào để thanh toán.');
        }
        // 3. VALIDATE STOCK (CHECK KHO LẦN CUỐI)
        // Duyệt qua từng item, check xem product->stock_qty có đủ không?
        foreach ($selectedItems as $item) {
            $product = $item->product; // Đã eager load ở CartService
            
            if (!$product) {
                throw new Exception("Sản phẩm (ID: {$item->product_id}) không còn tồn tại.");
            }
            // Check active
            if (!$product->is_active) {
                throw new Exception("Sản phẩm '{$product->name}' đang tạm ngưng kinh doanh.");
            }
            // Check số lượng
            if ($item->quantity > $product->stock_qty) {
                throw new Exception("Sản phẩm '{$product->name}' chỉ còn {$product->stock_qty} cái. Vui lòng giảm số lượng.");
            }
        }

        // 4. CHUẨN BỊ DATA SNAPSHOT (Sẵn sàng insert DB)
        return [
            'user_id'          => $user->id,
            'address_snapshot' => $address->toArray(), // Snapshot địa chỉ
            'note'             => $note,
            'payment_method'   => $paymentMethod,
            
            // Lấy trực tiếp từ kết quả Pricing đã tính ở bước 2
            'subtotal'         => $pricingResult['subtotal'],
            'shipping_fee'     => $pricingResult['shipping_fee'],
            'discount_amount'  => $pricingResult['discount_amount'],
            'grand_total'      => $pricingResult['total'], // Số tiền chốt để trừ ví
            'voucher_info'     => $pricingResult['voucher'] ?? null,
            
            // Dữ liệu items để insert vào bảng order_items
            // PricingService của bạn đã trả về 'items' (snapshot) chuẩn rồi, dùng luôn
            'order_items_snapshot' => $pricingResult['items'], 
            
            // Giữ lại object items gốc để logic trừ kho ở bước sau dùng (nếu cần)
            'eloquent_items'   => $selectedItems 
        ];
    }

    /**
     * Giao dịch tạo đơn hàng (Transaction)
     * @param array $orderData : Dữ liệu sạch lấy từ prepareOrderData()
     */
    public function createOrder($user, array $orderData)
    {
        return DB::transaction(function () use ($user, $orderData) {
            
            // 1. TẠO MÃ ĐƠN HÀNG TRƯỚC (Để log vào ví)
            do {
                $orderCode = 'ORD-' . strtoupper(Str::random(8));
            } while (Order::where('code', $orderCode)->exists());

            // 2. XỬ LÝ THANH TOÁN
            $paymentStatus = 'unpaid';
            if ($orderData['payment_method'] === 'wallet') {
                
                // Gọi hàm MỚI vừa viết (Không transaction con)
                // Nếu lỗi InsufficientBalance -> Nó throw Exception -> Transaction này Rollback -> OK
                $this->walletService->deductBalanceForOrder(
                    $user, 
                    $orderData['grand_total'], 
                    $orderCode, 
                    "Thanh toán đơn hàng $orderCode"
                );
                
                $paymentStatus = 'paid';
            }

            // 3. TẠO ORDER (MASTER)
            $order = Order::create([
                'code'             => $orderCode, // Dùng mã đã sinh
                'user_id'          => $user->id,
                'status'           => 'pending',
                'payment_status'   => $paymentStatus,
                'payment_method'   => $orderData['payment_method'],
                'shipping_address' => $orderData['address_snapshot'], 
                'note'             => $orderData['note'],
                'subtotal'         => $orderData['subtotal'],
                'shipping_fee'     => $orderData['shipping_fee'],
                'discount_amount'  => $orderData['discount_amount'],
                'total_amount'     => $orderData['grand_total']
            ]);

            // 4. TẠO ORDER ITEMS (SNAPSHOT)
            foreach ($orderData['order_items_snapshot'] as $itemSnapshot) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $itemSnapshot['product_id'],
                    'product_name'      => $itemSnapshot['product_name'],
                    'sku'               => $itemSnapshot['sku'] ?? null,
                    'price_at_purchase' => $itemSnapshot['price_at_purchase'],
                    'quantity'          => $itemSnapshot['quantity'],
                    'variant_snapshot'  => $itemSnapshot['variant_snapshot'] ?? null
                ]);
            }

            // 4.5. TRỪ TỒN KHO (INVENTORY DEDUCTION) - QUAN TRỌNG
            foreach ($orderData['order_items_snapshot'] as $itemSnapshot) {
                // LOCK FOR UPDATE: Khóa dòng sản phẩm này lại để trừ
                // Dùng decrement để trừ an toàn
                // where 'stock_qty' >= quantity để chặn Race Condition ngay tại lớp SQL
                $affectedRows = DB::table('products')
                    ->where('id', $itemSnapshot['product_id'])
                    ->where('stock_qty', '>=', $itemSnapshot['quantity']) // Điều kiện tiên quyết
                    ->decrement('stock_qty', $itemSnapshot['quantity']);

                if ($affectedRows === 0) {
                    // Nếu = 0 nghĩa là:
                    // Hoặc sản phẩm không tồn tại
                    // Hoặc số lượng tồn kho đã bị ai đó mua mất ngay trước tích tắc này
                    throw new Exception("Sản phẩm '{$itemSnapshot['product_name']}' vừa hết hàng. Vui lòng thử lại.");
                }
            }

            // 5. CLEAR CART
            $this->cartService->clearSelectedItems($user->id);

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
        $query->with(['items', 'user:id,name,email']);

        // 3. FILTERING 

        // Lọc theo Status
        if(!empty($filters['status'])){
            $query->where('status', $filters['status']);
        }

        // Lọc theo Payment Status
        if(!empty($filters['payment_status'])){
            $query->where('paymet_status', $filters['payment_status']);
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
            throw new Exception("Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.");
        }
        return $order;
    }
}