<?php 
namespace App\Services\Customer;

class CartService{
    public function addToCart(User $user, int $productId, int $quantity, array $options = []){
    
    // B1: Lấy Cart Header (như mục 1)
    $cart = Cart::FirstOrCreate($user->id)

    // B2: Check Tồn kho (Gọi Inventory Service/Product Model)
    // Nếu kho < $quantity -> Báo lỗi "Hết hàng" ngay.
    
    // B3: Kiểm tra xem món này ĐÃ TỒN TẠI trong giỏ chưa?
    // Điều kiện tìm: Cùng cart_id, Cùng product_id, VÀ Cùng options
    $existingItem = Tìm_Item_Trong_DB($cart->id, $productId, $options)

    If ($existingItem tồn tại) {
        // CASE A: Cập nhật số lượng
        // Logic: Số lượng mới = Số lượng cũ + Số lượng thêm vào
        $newQty = $existingItem->quantity + $quantity
        
        // (Optional) Check tồn kho lần nữa với số lượng tổng mới
        
        Update $existingItem set quantity = $newQty
    } 
    Else {
        // CASE B: Tạo dòng mới
        Tạo mới CartItem:
            - cart_id = $cart->id
            - product_id = $productId
            - quantity = $quantity
            - options = $options
            - selected = true
    }

    Return "Thêm thành công"
}
}