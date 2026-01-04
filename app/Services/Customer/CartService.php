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
     * Helper so sánh 2 mảng options (Bỏ qua thứ tự key)
     */
    private function compareOptions(array $a, array $b): bool{

        if(count($a) !== count($b)) return false;

        ksort($a);
        ksort($b);

        return $a === $b;
    }
}

