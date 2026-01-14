<?php 
namespace App\Services\Order;

use Exception;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Customer\CartService;
use App\Services\System\PricingService;
use App\Services\Customer\WalletService;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CheckoutService{

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
        $order = DB::transaction(function () use ($user, $orderData) {
            
            // 1. TẠO MÃ ĐƠN HÀNG TRƯỚC (ĐỂ LOG VÀO VÍ)
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

        // ==========================================================
        // 2. TRIGGER NOTIFICATION (Chỉ chạy khi Transaction OK)
        // ==========================================================
    
        // --- DEBUG LOG ---
        Log::info('Order created successfully. Order ID: ' . $order->id);
        Log::info('Preparing to notify user: ' . $user->id);

        try {
            $user->notify(new OrderCreatedNotification($order));
            Log::info('Notification dispatched!');
        }  catch (Exception $e) {
            Log::error('Notification Error: ' . $e->getMessage());
        }
        return $order;
    }
}