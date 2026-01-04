<?php 
namespace App\Services\Customer;

use Exception;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class CartService{

    /**
     * Thêm sản phẩm vào giỏ
     * @param int $userId
     * @param int $productId
     * @param int $quantity
     * @param array|null $options (VD: ['size' => 'M', 'color' => 'Red'])
     * @return CartItem
     */
    public function addToCart(User $userId, int $productId, int $quantity, array $options = []){
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
    public function updateCartItem($userId, $itemId, $newQuantity, $newOptions = null){
        return DB::transaction(function() use ($userId, $itemId, $newQuantity, $newOptions){
            // 1. AUTH & RETRIEVE (Bảo mật & Lấy dữ liệu)
            // Tìm item, đồng thời check xem item này có thuộc về cart của user này không
            // Eager load 'product' để check tồn kho sau này
            $currentItem = CartItem::with(['cart', 'product'])
                                    ->where('id', $itemId)
                                    ->whereHas('cart', function($query) use ($userId){
                                        $query->where('user_id', $userId);
                                    })->firstOrFail(); //404
            
            // 2. DETECT CHANGE TYPE (Phát hiện loại thay đổi)
            // Nếu user không gửi newOptions, hoặc newOptions giống hệt cũ -> Là update quantity thường
            $isOptionChanged = false;
            if($newOptions !== null && !$this->compareOptions((array)$currentItem->options, $newOptions)){
                $isOptionChanged = true;
            }

            // 3. LOGIC XỬ LÝ
            $finalItem = $currentItem;

            if($isOptionChanged){
                // Bước 3.1: Tìm xem trong giỏ đã có thằng nào mang Option Mới chưa?
                // (Trừ chính thằng currentItem ra)
                $duplicateItem = $this->findDuplicateItem(
                    $currentItem->cart_id,
                    $currentItem->product_id,
                    $newOptions,
                    $currentItem->id
                );

                if($duplicateItem){
                    // CASE B2: MERGE (Gộp dòng)
                    // Cộng số lượng user muốn update vào thằng đã tồn tại
                    $mergeQty = $duplicateItem->quantity + $newQuantity;
                    // Check tồn kho
                    if($currentItem->product->stock_qty < $mergeQty){
                        throw new Exception("Tổng số lượng trong giỏ vượt quá tồn kho.");
                    }
                    // Update thằng kia, xóa thằng này
                    $duplicateItem->update(['quantity' => $mergeQty]);
                    $currentItem->delete();

                    $finalItem = $duplicateItem;
                }
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