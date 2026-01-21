<?php 
namespace App\Services\Customer;

use Exception;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use App\Services\System\PricingService;

class CartService{

    public function __construct(protected PricingService $pricingService){}

    /**
     * Thêm sản phẩm vào giỏ
     * @param int $userId
     * @param int $productId
     * @param int $quantity
     * @param array|null $options (VD: ['size' => 'M', 'color' => 'Red'])
     * @return CartItem
     */
    public function addToCart($userId, int $productId, int $quantity, array $options = []){
        return DB::transaction(function() use ($userId, $productId, $quantity, $options){
            //B1: Lấy Cart Header
            $cart = Cart::firstOrCreate(['user_id'=> $userId]);

            //B2:Check tồn kho
            $product = Product::findOrfail($productId);

            if($product->stock_qty< $quantity){
                throw new Exception("Sản phẩm {$product->name} chỉ còn {$product->stock_qty}. Vui lòng điều chỉnh số lượng!");
            }

            //B3: Kiểm tra sản phẩm đã tồn tại trong giỏ hàng chưa
            // Lấy TẤT CẢ sản phẩm trong giỏ ra
            $cartItem = CartItem::where('cart_id', $cart->id)
                                ->where('product_id', $productId)
                                ->get();

            $existingItem = null;
            foreach($cartItem as $item){
                if ($this->compareOptions((array)$item->options, $options)){
                    $existingItem =$item;
                    break;
                }
            }

            if($existingItem){
                // CASE A: Cập nhật số lượng
                $newQty = $existingItem->quantity + $quantity;
                // Check tồn kho lần nữa cho tổng số lượng mới
                if ($product->stock_qty < $newQty){
                    throw new Exception("Tổng số lượng trong giỏ vượt quá tồn kho.");
                }

                $existingItem->update(['quantity' => $newQty]);
                return $existingItem;
            }

            // CASE B: Thêm mới
            return CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $productId,
                'quantity'   => $quantity,
                'options'    => $options,
                'selected'   => true,
            ]);
        });
    }

    /**
    * Update Cart Item (Quantity & Options)
    * @param int $userId
    * @param int $itemId (ID của dòng trong cart_items)
    * @param int $newQuantity
    * @param array|null $newOptions (Nếu null nghĩa là không đổi option)
    */
    public function updateCartItem(int $userId, int $itemId, ?int $newQuantity = null, ?array $newOptions = null)
    {
        return DB::transaction(function () use ($userId, $itemId, $newQuantity, $newOptions) {
            
            // 1. AUTH & RETRIEVE
            $currentItem = CartItem::with(['cart', 'product'])
                ->where('id', $itemId)
                ->whereHas('cart', fn($q) => $q->where('user_id', $userId))
                ->firstOrFail();

            // 2. CHUẨN HÓA DỮ LIỆU (FALLBACK)
            // Nếu input null -> Dùng giá trị cũ
            $targetQuantity = $newQuantity ?? $currentItem->quantity;
            
            $currentOptions = (array) $currentItem->options;
            $targetOptions  = $newOptions ?? $currentOptions;

            // 3. DETECT CHANGE (Phát hiện thay đổi)
            // Chỉ cần so sánh 1 lần duy nhất ở đây
            $isOptionChanged = !$this->compareOptions($currentOptions, $targetOptions);

            // Biến lưu item kết quả cuối cùng
            $finalItem = $currentItem;

            if ($isOptionChanged) {
                // --- NHÁNH A: CÓ THAY ĐỔI OPTION ---

                // Tìm xem có trùng với item nào khác trong giỏ không (trừ chính nó)
                $duplicateItem = $this->findDuplicateItem(
                    $currentItem->cart_id,
                    $currentItem->product_id,
                    $targetOptions, // Dùng targetOptions đã chuẩn hóa
                    $currentItem->id
                );

                if ($duplicateItem) {
                    // CASE A1: MERGE (Gộp dòng)
                    // Logic: Số lượng hiện có của dòng kia + Số lượng MỚI user muốn set cho dòng này
                    $mergedQty = $duplicateItem->quantity + $targetQuantity;

                    // Check tồn kho tổng
                    if ($currentItem->product->stock_qty < $mergedQty) {
                        throw new Exception("Không đủ hàng để gộp (Kho còn: {$currentItem->product->stock_qty}).");
                    }

                    // Update thằng kia, xóa thằng này
                    $duplicateItem->update(['quantity' => $mergedQty]);
                    $currentItem->delete();

                    $finalItem = $duplicateItem;
                } else {
                    // CASE A2: ĐỔI OPTION (Không trùng ai cả -> Chỉ rename)
                    // Check tồn kho cho số lượng mới
                    if ($currentItem->product->stock_qty < $targetQuantity) {
                        throw new Exception("Sản phẩm chỉ còn {$currentItem->product->stock_qty} món.");
                    }

                    $currentItem->update([
                        'quantity' => $targetQuantity,
                        'options'  => $targetOptions
                    ]);
                    $finalItem = $currentItem;
                }

            } else {
                // --- NHÁNH B: CHỈ THAY ĐỔI SỐ LƯỢNG (Giữ nguyên Option) ---
                // Đây là đoạn code bạn bị THIẾU trong bản cũ
                
                // Nếu số lượng không đổi thì thôi return luôn cho nhanh
                if ($currentItem->quantity === $targetQuantity) {
                    return $currentItem;
                }

                // Check tồn kho
                if ($currentItem->product->stock_qty < $targetQuantity) {
                    throw new Exception("Sản phẩm chỉ còn {$currentItem->product->stock_qty} món.");
                }

                $currentItem->update(['quantity' => $targetQuantity]);
                $finalItem = $currentItem;
            }

            return $finalItem;
        });
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ
     * @param int $userId
     * @param int $itemId (ID của dòng trong bảng cart_items)
     * @return bool
     */
    public function removeItem($userId, $itemId){
        // Tìm item, đồng thời check xem item này có thuộc về cart của user này không
        $item = CartItem::where('id' , $itemId)
                        ->whereHas('cart', function($query) use ($userId){
                            $query->where('user_id', $userId);
                        })->first();

        if(!$item){
            throw new Exception("Không tìm thấy sản phẩm trong giỏ hàng.");
        }

        return $item->delete();
    }

    /**
     * Chỉ xóa những sản phẩm ĐÃ CHỌN (selected = true)
     * Dùng sau khi Checkout thành công hoặc User bấm "Xóa các mục đã chọn"
     */
    public function clearSelectedItems($userId){
        $cart = Cart::where('user_id', $userId)->first();
        if ($cart) {
            // Chỉ xóa những item được đánh dấu là selected (đã mua)
            $cart->items()->where('selected', true)->delete();
        }
        return false;
    }

    /**
     * Lấy chi tiết giỏ hàng và tính toán tiền nong
     * @param int $userId
     * @param array $params (Chứa voucher_code, address_id nếu có)
     */
    public function getCartDetail($userId, $params = []){
        // 1. Load quan hệ 'items' (SỐ NHIỀU)
        $cart = Cart::with(['items' => function($query) {
        // Load product kèm theo các file liên quan thông qua quan hệ đa hình [cite: 11, 24, 66]
        $query->where('selected', true)->with('product.images');
        }])->firstOrCreate(['user_id' => $userId]);

        // 2. Lấy items (SỐ NHIỀU)
        // [FIX LỖI NULL]: Thêm ?? collect([]) để phòng trường hợp relation trả về null
        $cartItems = $cart->items ?? collect([]); 

        // 3. Filter selected
        $selectedItems = $cartItems->where('selected', true);

        // 4. Map dữ liệu
        $pricingItems = $selectedItems->map(function($item){
            return [
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'options'    => $item->options
            ];
        })->toArray();

        // 5. Gọi PricingService
        $pricingResult = $this->pricingService->calculateCart(
            $pricingItems,
            $params['voucher_code'] ?? null,
            $userId,
            $params['address_id'] ?? null
        );

        return [
            'cart'    => $cart,
            'pricing' => $pricingResult
        ];
    }

    /**
     * ========================
     *  HELPER METHOD
     * =========================
     */

    /**
     * Helper so sánh 2 mảng options (Bỏ qua thứ tự key)
     */
    private function compareOptions(array $a, array $b): bool{
        if(count($a) !== count($b)) return false;
        ksort($a);
        ksort($b);
        return $a === $b;
    }

    /**
    * Helper tìm item trùng option (Re-use logic so sánh)
    */
    private function findDuplicateItem($cartId, $productId, $targetOptions, $excludeItemId){
        $candidates = CartItem::where('cart_id', $cartId)
                                ->where('product_id', $productId)
                                ->where('id', '!=', $excludeItemId)
                                ->get();

        foreach ($candidates as $candidate) {
            if ($this->compareOptions((array)$candidate->options, $targetOptions)) {
                return $candidate;
            }
        }

        return null;
    }
}